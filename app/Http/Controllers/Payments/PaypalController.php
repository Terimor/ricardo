<?php

namespace App\Http\Controllers\Payments;

use App\Http\Requests\PayPalCrateOrderRequest;
use App\Http\Requests\PayPalVerfifyOrderRequest;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\PayPalService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $response = $this->payPalService->createOrder($request);
        $braintree_response = $response['braintree_response'];
        $odin_order_id = $response['odin_order_id'];

        $response = json_encode($braintree_response->result);
        unset($braintree_response->headers['Set-Cookie']);
        $braintree_response->headers['Content-Length'] = strlen($response);

        return [
            'id' => optional($braintree_response->result)->id,
            'odin_order_id' => $odin_order_id
        ];
    }

    /**
     * @param PayPalVerfifyOrderRequest $request
     * @return array
     */
    public function verifyOrder(PayPalVerfifyOrderRequest $request)
    {
        return $this->payPalService->verifyOrder($request);
    }

    /**
     * @return $this
     */
    public function checkout()
    {
        return view('test-checkout')->with([
            'product' => resolve(ProductService::class)->resolveProduct(request()),
            'paypal_client' => Setting::getValue('instant_payment_paypal_client_id')
        ]);
    }

    /**
     * @param Request $request
     */
    public function webhooks(Request $request)
    {
        $this->payPalService->webhooks($request);
    }
}
