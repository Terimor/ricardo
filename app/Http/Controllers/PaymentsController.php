<?php

namespace App\Http\Controllers;

use App\Services\CheckoutDotComService;
use App\Services\PaymentService;
use App\Http\Requests\PaymentCardCreateOrderRequest;
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

    public function capturedWebhook(Request $req) {
        logger()->error("Checkout.captured.webhook", ['req' => $req->toArray()]);
        return [ 'status' => 'ok' ];
    }
}
