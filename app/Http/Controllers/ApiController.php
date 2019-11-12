<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Http\Requests\CaptureOrVoidPaymentApiRequest;

class ApiController extends Controller
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
     * Captures payment
     * @param  CaptureOrVoidPaymentApiRequest $req
     * @return array
     */
    public function capturePayment(CaptureOrVoidPaymentApiRequest $req, string $order_id, string $txn_hash)
    {
        return [
            'status' => $this->paymentService->capture($order_id, $txn_hash)
        ];
    }

    /**
     * Voids payment
     * @param  CaptureOrVoidPaymentApiRequest $req
     * @return array
     */
    public function voidPayment(CaptureOrVoidPaymentApiRequest $req, string $order_id, string $txn_hash)
    {
        return [
            'status' => $this->paymentService->void($order_id, $txn_hash)
        ];
    }

}
