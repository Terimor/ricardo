<?php

namespace App\Http\Controllers\Payments;

use App\Http\Requests\PayPalCrateOrderRequest;
use App\Http\Requests\PayPalVerfifyOrderRequest;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\PayPalService;
use App\Services\ProductService;
use Illuminate\Http\Request;

/**
 * Class PaypalController
 * @package App\Http\Controllers\Payments
 */
class PaypalController extends Controller
{
    /**
     * @var PayPalService
     */
    protected $payPalService;

    /**
     * PaypalController constructor.
     * @param PayPalService $payPalService
     */
    public function __construct(PayPalService $payPalService)
    {
        $this->payPalService = $payPalService;
    }

    /**
     * @param PayPalCrateOrderRequest $request
     * @return array
     */
    public function createOrder(PayPalCrateOrderRequest $request)
    {
        //fix for PayPal payments
        ini_set('serialize_precision', 15);

        $response = $this->payPalService->createOrder($request);
        $braintree_response = $response['braintree_response'];
        $odin_order_id = $response['odin_order_id'];
        $currency = !empty($response['order_currency']) ? $response['order_currency'] : '';
        $order_number = $response['order_number'];

        if ($braintree_response) {
            $response = isset($braintree_response->result) ? json_encode($braintree_response->result) : null;
            unset($braintree_response->headers['Set-Cookie']);
            $braintree_response->headers['Content-Length'] = strlen($response);
        }
        return [
            'id' => isset($braintree_response->result) ? optional($braintree_response->result)->id : null,
            'odin_order_id' => $odin_order_id,
            'order_currency' => $currency,
            'order_number' => $order_number,
        ];
    }

    /**
     * @param PayPalVerfifyOrderRequest $request
     * @return array
     */
    public function verifyOrder(PayPalVerfifyOrderRequest $request)
    {
        //fix for PayPal payments
        ini_set('serialize_precision', 15);

        return $this->payPalService->verifyOrder($request);
    }

    /**
     * @param Request $request
     */
    public function webhooks(Request $request)
    {
        $this->payPalService->webhooks($request);
    }
}
