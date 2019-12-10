<?php

namespace App\Http\Controllers;

use App\Services\BluesnapService;
use App\Services\CurrencyService;
use App\Services\CheckoutDotComService;
use App\Services\EbanxService;
use App\Services\MinteService;
use App\Services\PaymentService;
use App\Exceptions\AuthException;
use App\Http\Requests\PaymentCardCreateOrderRequest;
use App\Http\Requests\PaymentCardMinte3dsRequest;
use App\Http\Requests\PaymentCardOrderErrorsRequest;
use App\Http\Requests\PaymentCardCreateUpsellsOrderRequest;
use App\Http\Requests\GetPaymentMethodsByCountryRequest;
use App\Models\Txn;
use App\Models\OdinOrder;
use App\Constants\PaymentMethods;
use App\Constants\PaymentProviders;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    /**
     * @var PaymentService
     */
    protected $paymentService;

    /**
     * Create a new controller instance.
     * @param PaymentService $paymentService
     * @return void
     */
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Creates card payment
     * @param  PaymentCardCreateOrderRequest $req
     * @return array
     */
    public function createCardOrder(PaymentCardCreateOrderRequest $req)
    {
        $reply = $this->paymentService->createOrder($req);

        $result = [
            'order_currency'    => $reply['order_currency'],
            'order_number'      => $reply['order_number'],
            'order_id'          => $reply['order_id'],
            'id'                => $reply['id'],
            'status'            => $reply['status']
        ];

        if (!empty($reply['errors'])) {
            $result['errors'] = $reply['errors'];
            PaymentService::cacheErrors([
                'number'    => $reply['order_number'],
                'errors'    => $reply['errors']
            ]);
        }
        if (!empty($reply['redirect_url'])) {
            $result['redirect_url'] = stripslashes($reply['redirect_url']);
        }

        return $result;
    }

    /**
     * Creates card upsells payment
     * @param  PaymentCardCreateUpsellsOrderRequest $req
     * @return array
     */
    public function createCardUpsellsOrder(PaymentCardCreateUpsellsOrderRequest $req)
    {
        $reply = $this->paymentService->createUpsellsOrder($req);
        return [
            'order_currency'    => $reply['order_currency'],
            'order_number'      => $reply['order_number'],
            'order_id'          => $reply['order_id'],
            'id'                => $reply['id'],
            'status'            => $reply['status'],
            'upsells'           => $reply['upsells']
        ];
    }

    /**
     * Returns order errors
     * @param  PaymentCardOrderErrorsRequest $req
     * @return array
     */
    public function getCardOrderErrors(PaymentCardOrderErrorsRequest $req)
    {
        $order_id = $req->get('order');
        $reply = PaymentService::getOrderErrors($order_id);
        return [
            'order_id'  => $order_id,
            'errors'    => $reply ?? []
        ];
    }

    /**
     * Returns payment methods by country
     * @param  GetPaymentMethodsByCountryRequest $req [description]
     * @return array
     */
    public function getPaymentMethodsByCountry(GetPaymentMethodsByCountryRequest $req)
    {
        return collect(PaymentService::getPaymentMethodsByCountry($req->get('country')))->collapse()->all();
    }

    /**
     * Bluesnap webhook endpoint
     * @param  Request $req
     * @return string
     */
    public function bluesnapWebhook(Request $req): string
    {
        $type = $req->input('transactionType');

        if (!in_array($type, [BluesnapService::TYPE_WEBHOOK_CHARGE, BluesnapService::TYPE_WEBHOOK_DECLINE])) {
            logger()->info('Bluesnap unprocessed webhook', ['content' => $req->getContent()]);
        }

        $bluesnap = new BluesnapService();
        $reply = $bluesnap->validateWebhook($req);

        if (!$reply['status']) {
            logger()->error('Bluesnap unauthorized webhook', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new AuthException('Unauthorized');
        }

        $this->paymentService->approveOrder($reply['txn']);

        return $reply['result'];
    }

    /**
     * Accepts checkout.com charges.captured webhook
     * @param  Request $req
     * @return void
     */
    public function checkoutDotComCapturedWebhook(Request $req)
    {
        $checkoutService = new CheckoutDotComService();
        $reply = $checkoutService->validateCapturedWebhook($req);

        if (!$reply['status']) {
            logger()->error('checkout.com unauthorized captured webhook', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new AuthException('checkout.com captured webhook unauthorized');
        }

        $this->paymentService->approveOrder($reply['txn']);
    }

    /**
     * Checkout.com failed webhook
     * @param  Request $req
     * @return void
     */
    public function checkoutDotComFailedWebhook(Request $req): void
    {
        $checkoutService = new CheckoutDotComService();
        $reply = $checkoutService->validateFailedWebhook($req);

        if (!$reply['status']) {
            logger()->error('checkout.com unauthorized failed webhook', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new AuthException('checkout.com captured webhook unauthorized');
        }

        $this->paymentService->rejectTxn($reply['txn']);

        PaymentService::cacheErrors($reply['txn']);
    }

    /**
     * Ebanx notification
     * @param  Request $req
     * @return void
     */
    public function ebanxWebhook(Request $req)
    {
        $ebanxService = new EbanxService();
        $reply = $ebanxService->validateWebhook($req);

        if (!$reply['status']) {
            logger()->error('Ebanx unauthorized webhook', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new AuthException('Notification unauthorized');
        }

        foreach ($reply['hashes'] as $hash) {
            $payment = $ebanxService->requestStatusByHash($hash);
            if (!empty($payment['number'])) {
                $this->paymentService->approveOrder($payment);
            } else {
                logger()->warning('Ebanx payment not found', ['hash' => $hash]);
            }
        }
    }

    /**
     * Mint-e redirect after 3ds
     * @param  Request $req
     * @param string $order_id
     * @return void
     */
    public function minte3ds(PaymentCardMinte3dsRequest $req, string $order_id)
    {
        $auth_status = $req->input('status');
        $txn_hash    = $req->input('transid');

        logger()->info('Mint-e 3ds redirect debug', ['ip' => $req->ip(), 'body' => $req->getContent()]);

        $order = OdinOrder::getById($order_id); // throwable
        $order_txn = $order->getTxnByHash($txn_hash); // throwable

        $mint = new MinteService();
        $reply = $mint->captureAfterAuth3ds($req, ['order_id' => $order_id, 'payment_api_id' => $order_txn['payment_api_id']]);

        if (!$reply['status']) {
            logger()->error('Mint-e unauthorized redirect', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new AuthException('Unauthorized');
        }

        $query = ['order' => $order_id];
        if ($reply['txn']['status'] === Txn::STATUS_APPROVED) {
            $this->paymentService->approveOrder($reply['txn']);
            $query['3ds'] = 'success';
        } else {
            $order = $this->paymentService->rejectTxn($reply['txn']);
            PaymentService::getCardToken($order->number); // remove token
            PaymentService::cacheErrors(array_merge($reply['txn'], ['number' => $order->number]));
            $query['3ds'] = 'failure';
        }

        return redirect('/checkout?' . $qs = http_build_query($query));
    }

    public function test(PaymentCardCreateOrderRequest $req)
    {
        if (\App::environment() === 'production') {
            throw new AuthException('Unauthorized');
        }
        $mint = new MinteService();
        $api = $mint->getPaymentApi(['product_id' => '5d7b6b426962ed6c260b7994']);
        return $api->toArray();
        $reply = $this->paymentService->createMinteOrder($req);

        $result = [
            'order_currency'    => $reply['order_currency'],
            'order_number'      => $reply['order_number'],
            'order_id'          => $reply['order_id'],
            'id'                => $reply['id'],
            'status'            => $reply['status']
        ];

        if (!empty($reply['errors'])) {
            $result['errors'] = $reply['errors'];
            PaymentService::cacheErrors([
                'number'    => $reply['order_number'],
                'errors'    => $reply['errors']
            ]);
        }

        if (!empty($reply['redirect_url'])) {
            $result['redirect_url'] = stripslashes($reply['redirect_url']);
        }

        return $result;
    }

}
