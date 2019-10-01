<?php

namespace App\Http\Controllers;

use App\Services\CheckoutDotComService;
use App\Services\PaymentService;
use App\Exceptions\AuthException;
use App\Http\Requests\PaymentCardCreateOrderRequest;
use App\Http\Requests\CheckoutDotComCapturedWebhookRequest;
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

    public function createCardOrder(PaymentCardCreateOrderRequest $req) {
        return $this->paymentService->createOrder($req);
    }

    public function checkoutDotComCapturedWebhook(Request $req) {

        $checkoutService = new CheckoutDotComService();
        $reply = $checkoutService->validateCaptureWebhook($req);

        if (!$reply['status']) {
            logger()->error('Checkout.com unauthorized captured webhook', [ 'ip' => $req->ip() ]);
            throw new AuthException('Checkout.com captured webhook unauthorized');
        }

        $this->paymentService->approveOrder($reply);
    }
}
