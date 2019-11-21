<?php

namespace App\Http\Controllers;

use App\Services\BluesnapService;
use App\Services\CheckoutDotComService;
use App\Services\EbanxService;
use App\Services\PaymentService;
use App\Exceptions\AuthException;
use App\Http\Requests\PaymentCardCreateOrderRequest;
use App\Http\Requests\PaymentCardOrderErrorsRequest;
use App\Http\Requests\PaymentCardCreateUpsellsOrderRequest;
use App\Http\Requests\GetPaymentMethodsByCountryRequest;
use Illuminate\Http\Request;

/*use com\checkout;
use com\checkout\ApiServices;*/

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

    public function test(PaymentCardCreateOrderRequest $req)
    {
        $method = \App\Mappers\PaymentMethodMapper::toMethod($req->input('card.number'));
        return PaymentService::getProviderByCountryAndMethod($req->input('address.country'), $method, true);
    }
}
