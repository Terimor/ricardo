<?php

namespace App\Http\Controllers;

use App\Services\CheckoutDotComService;
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
        // create new order
        $reply = $this->paymentService->createOrder($req);

        //request and cache token
        if ($reply['status'] === PaymentService::STATUS_OK) {
            $contact = array_merge($req->get('address'), $req->get('contact'));
            $token = $this->paymentService->requestCardToken($req->get('card'), $contact, $reply['provider']);
            PaymentService::setCardToken($token, $reply['order']->getIdAttribute());;
        }

        return [
            'order_currency'    => $reply['order']->currency,
            'order_id'          => $reply['order']->getIdAttribute(),
            'status'            => $reply['status'],
            'redirect_url'      => stripslashes($reply['redirect_url'])
        ];
    }

    /**
     * Creates card upsells payment
     * @param  PaymentCardCreateUpsellsOrderRequest $req
     * @return array
     */
    public function createCardUpsellsOrder(PaymentCardCreateUpsellsOrderRequest $req)
    {
        return $this->paymentService->createUpsellsOrder($req);
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

        $this->paymentService->approveOrder($reply);
    }

    public function checkoutDotComFailedWebhook(Request $req)
    {
        logger()->info('checkout.com', ['content' => json_encode($req->toArray())]);
    }

}
