<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Http\Requests\RefundPaymentApiRequest;
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
    public function capturePayment(CaptureOrVoidPaymentApiRequest $req, string $order_id, string $txn_hash): array
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
    public function voidPayment(CaptureOrVoidPaymentApiRequest $req, string $order_id, string $txn_hash): array
    {
        return [
            'status' => $this->paymentService->void($order_id, $txn_hash)
        ];
    }

    /**
     * Tries to refund payment
     * @param  RefundPaymentApiRequest $req
     * @param  string                  $order_id
     * @param  string                  $txn_hash
     * @return array
     */
    public function refundPayment(RefundPaymentApiRequest $req, string $order_id, string $txn_hash): array
    {
        $reason = $req->input('reason');
        $amount = $req->input('amount', null);
        $result = $this->paymentService->refund($order_id, $txn_hash, $reason, $amount);

        return array_merge($result, ['hash' => $txn_hash]);
    }

}
