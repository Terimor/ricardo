<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CardService;
use App\Http\Requests\RefundPaymentApiRequest;
use App\Http\Requests\CaptureOrVoidPaymentApiRequest;

class ApiController extends Controller
{
    /**
     * Captures payment
     * @param  CaptureOrVoidPaymentApiRequest $req
     * @return array
     */
    public function capturePayment(CaptureOrVoidPaymentApiRequest $req, string $order_id, string $txn_hash): array
    {
        return [
            'status' => CardService::capture($order_id, $txn_hash)
        ];
    }

    /**
     * Voids payment
     * @param  CaptureOrVoidPaymentApiRequest $req
     * @return array
     */
    public function voidPayment(CaptureOrVoidPaymentApiRequest $req, string $order_id, string $txn_hash): array
    {
        return ['status' => CardService::void($order_id, $txn_hash)];
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
        $result = CardService::refund($order_id, $txn_hash, $reason, $amount);
        return array_merge($result, ['hash' => $txn_hash]);
    }

}
