<?php

namespace App\Http\Controllers;

use App\Services\BluesnapService;
use App\Services\EbanxService;
use App\Services\PaymentService;
use App\Exceptions\AuthException;
use App\Http\Requests\PaymentCardCreateOrderRequest;
use App\Http\Requests\PaymentCardMinte3dsRequest;
use App\Http\Requests\PaymentCardOrderErrorsRequest;
use App\Http\Requests\PaymentCardCreateUpsellsOrderRequest;
use App\Http\Requests\GetPaymentMethodsByCountryRequest;
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
        $authKey = $req->input('authKey', '');
        $type    = $req->input('transactionType');

        if (!in_array($type, [BluesnapService::TYPE_WEBHOOK_CHARGE, BluesnapService::TYPE_WEBHOOK_DECLINE])) {
            logger()->info('Bluesnap unprocessed webhook', ['content' => $req->getContent()]);
        }

        $bluesnap = new BluesnapService();
        $reply = $bluesnap->validateWebhook($req);

        if (!$reply['status']) {
            logger()->error('Bluesnap unauthorized webhook', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new AuthException('Unauthorized');
        }

        $order = $this->paymentService->approveOrder($reply['txn']);
        $order_txn = $order->getTxnByHash($reply['txn']['hash']);

        return md5($authKey . 'ok' . $bluesnap->getDataProtectionKey($order_txn));
    }

    /**
     * Accepts checkout.com charges.captured webhook
     * @param  Request $req
     * @return void
     */
    public function checkoutDotComCapturedWebhook(Request $req)
    {
        $order_number = $req->input('data.reference');

        if (!$order_number) {
            logger()->error('checkout.com malformed captured webhook', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new \Exception('checkout.com malformed captured webhook');
        }

        $checkout = $this->paymentService->getCheckoutService($order_number);

        $reply = $checkout->validateCapturedWebhook($req);

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
        $order_number = $req->input('data.reference', '');

        if (!$order_number) {
            logger()->error('checkout.com malformed failed webhook', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new \Exception('checkout.com malformed failed webhook');
        }

        $checkout = $this->paymentService->getCheckoutService($order_number);

        $reply = $checkout->validateFailedWebhook($req);

        if (!$reply['status']) {
            logger()->error('checkout.com unauthorized failed webhook', ['ip' => $req->ip(), 'body' => $req->getContent()]);
            throw new AuthException('checkout.com failed webhook unauthorized');
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

        $this->paymentService->ebanxNotification($reply['hashes']);
    }

    /**
     * Mint-e redirect after 3ds
     * @param PaymentCardMinte3dsRequest $req
     * @param string $order_id
     * @return void
     */
    public function minte3ds(PaymentCardMinte3dsRequest $req, string $order_id)
    {
        logger()->info('Mint-e 3ds redirect debug', ['ip' => $req->ip(), 'body' => $req->getContent()]);

        $query = [
            'order' => $order_id,
            '3ds'   => $this->paymentService->minte3ds($req, $order_id) ? 'success' : 'failure'
        ];

        return redirect('/checkout?' . $qs = http_build_query($query));
    }

    public function test(PaymentCardCreateOrderRequest $req)
    {
        if (\App::environment() === 'production') {
            throw new AuthException('Unauthorized');
        }
        return 'ok';
    }

}
