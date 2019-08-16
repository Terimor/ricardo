<?php

namespace App\Http\Controllers\Payments;

use App\Http\Requests\PayPalCrateOrderRequest;
use App\Http\Requests\PayPalVerfifyOrderRequest;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\PayPalService;
use App\Services\ProductService;
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
     * @return Response
     */
    public function createOrder(PayPalCrateOrderRequest $request)
    {
        $braintree_response = $this->payPalService->createOrder($request);
        $response = json_encode($braintree_response->result);
        unset($braintree_response->headers['Set-Cookie']);
        $braintree_response->headers['Content-Length'] = strlen($response);
        return new Response(
            $response,
            $braintree_response->statusCode,
            $braintree_response->headers
        );
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
            'paypal_client' => optional(Setting::where('key', 'paypal_client_id')->first())->value
        ]);
    }

}
