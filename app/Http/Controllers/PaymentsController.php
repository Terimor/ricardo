<?php

namespace App\Http\Controllers;

use App\Services\CheckoutDotComService;
use App\Services\EbanxNewService;
use App\Services\PaymentService;
use App\Exceptions\AuthException;
use App\Http\Requests\PaymentCardCreateOrderRequest;
use App\Http\Requests\CheckoutDotComCapturedWebhookRequest;
use App\Http\Requests\PaymentCardCreateUpsellsOrderRequest;
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

        if (!empty($reply['status_code'])) {
            $result['status_code'] = $reply['status_code'];
            $result['status_desc'] = $reply['status_desc'] ?? 'Unknown error';
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
     * Accepts checkout.com charges.captured webhook
     * @param  Request $req
     * @return void
     */
    public function checkoutDotComCapturedWebhook(Request $req)
    {
        $checkoutService = new CheckoutDotComService();
        $reply = $checkoutService->validateCapturedWebhook($req);

        logger()->info('checkout.com', ['reply' => json_encode($req->toArray())]);

        if (!$reply['status']) {
            logger()->error('checkout.com unauthorized captured webhook', [ 'ip' => $req->ip() ]);
            throw new AuthException('checkout.com captured webhook unauthorized');
        }

        $this->paymentService->approveOrder($reply['txn']);
    }

    public function checkoutDotComFailedWebhook(Request $req)
    {
        logger()->info('checkout.com', ['content' => json_encode($req->toArray())]);
    }

    public function testEbanx(PaymentCardCreateOrderRequest $req)
    {
        $reply = $this->paymentService->testCreateOrder($req);
        return [
            'order_currency'    => $reply['order_currency'],
            'order_number'      => $reply['order_number'],
            'order_id'          => $reply['order_id'],
            'id'                => $reply['id'],
            'status'            => $reply['status']
        ];
    }

    public function testEbanxWebhook(Request $req)
    {
        $ebanxService = new EbanxNewService();
        $reply = $ebanxService->validateWebhook($req);

        logger()->info('ebanx notification', [
            'body' => json_encode($req->getContent()),
            'headers' => json_encode($req->header())
        ]);

        if (!$reply['status']) {
            logger()->error('Ebanx unauthorized webhook', [ 'ip' => $req->ip() ]);
            throw new AuthException('Notification unauthorized');
        }

        foreach ($reply['hashes'] as $hash) {
            $this->paymentService->approveOrder($ebanxService->requestStatusByHash($hash));
        }
    }
}
