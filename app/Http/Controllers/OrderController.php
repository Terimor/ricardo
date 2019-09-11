<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderService;
use StdClass;
use App\Models\Setting;
use App\Models\OdinOrder;


/*use com\checkout;
use com\checkout\ApiServices;*/

class OrderController extends Controller
{

    protected $orderService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     *
     * @param Request $request
     * @return json
     */
    public function saveTxn(Request $request)
    {
        $data = [
            'hash' => \Utils::randomString(10),
            'value' => \Utils::randomNumber(3) + mt_rand() / mt_getrandmax(),
            'currency' => \Utils::randomString(2),
            'provider_data' => [
                'data_1' => \Utils::randomString(6),
                'data_2' => \Utils::randomString(6),
            ],
        ];

        $data = $request->all();
        $result = $this->orderService->addTxn($data);

        return response()->json([
            $result
        ], (!isset($result['success']) || !$result['success']) ? 402 : 200);
    }

    /**
     *
     * @param Request $request
     * @return type
     */
    public function saveOrder(Request $request)
    {

        $data = [
            'number' => OdinOrder::generateOrderNumber(),
            'status' => 'new',
            'currency' => \Utils::randomString(3),
            'total_paid' => \Utils::randomNumber(3) + mt_rand() / mt_getrandmax(),
            //'payment_hash' => '', // string
            //'payment_provider' => '', // enum string
            //'payment_method' => '', // enum string
            'customer_id' => \Utils::randomNumber(6),
            'customer_email' => \Utils::randomString(10).'@'.\Utils::randomString(3).'.com',
            'customer_first_name' => \Utils::randomString(10),
            'customer_last_name' => \Utils::randomString(10),
            'customer_phone' => \Utils::randomString(10),
            'language' => '', app()->getLocale(),
            //'ip' => '', // string
            'shipping_country' => \Utils::randomString(2),
            'shipping_zip' => \Utils::randomString(6),
            'shipping_state' => \Utils::randomString(6),
            'shipping_city' => \Utils::randomString(6),
            'shipping_street' => \Utils::randomString(6),
            //'exported' => false, // bool, default false
            //'warehouse_id' => '',
            'trackings' => [
                'number' => \Utils::randomString(6),
                //'aftership_slug' => '', // enum string
            ],
            'products' => [
                'sku_code' => \Utils::randomString(6),
                'quantity' => \Utils::randomNumber(1),
                'price' => \Utils::randomNumber(3) + mt_rand() / mt_getrandmax(),
                'price_usd' => \Utils::randomNumber(3) + mt_rand() / mt_getrandmax(),
                //'is_main' => '', // bool
            ],
            //'ipqualityscore' => '', // object
            //'page_checkout' => '', // string
            //'flagged' => false, // bool, default false
            //'offer' => '', // string
            //'affiliate' => '', // string
            /*'txns' => [
                'txn_id' => '', // Txn id
                'hash' => '', // string
                'value' => '', // float
                'approved' => '', // bool
                'refunded' => false, // bool
                'charged_back' => false, // bool
            ],*/
            //'is_refunding' => false, // bool, default false,
        ];

        $data = $request->all();

        $result = $this->orderService->addOdinOrder($data);
        return response()->json([
            $result
        ], (!isset($result['success']) || !$result['success']) ? 402 : 200);
    }

    /**
     * Save customer
     * @param Request $request
     * @return json
     */
    public function saveCustomer(Request $request)
    {
        $data = [
            'email' => \Utils::randomString(2).'bh4prl7qvw@j6c.com',
            'first_name' =>  \Utils::randomString(10),
            'last_name' =>  \Utils::randomString(10),
            'phone' =>  \Utils::randomString(10),
            //"addresses" =>  [
                "country" => '12',
                "zip" => '1',
                "state" => '1',
                "city" => '1',
                "street" => '1',
                "street2" => '1'
            //],

        ];

        //$data = $request->all();
        $result = $this->orderService->addCustomer($data);

        return response()->json([
            $result
        ], (!isset($result['success']) || !$result['success']) ? 402 : 200);
    }

}
