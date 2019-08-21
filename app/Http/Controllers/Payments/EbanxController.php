<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Setting;
use App\Http\Requests\EbanxSendTransactionRequest;
use App\Services\CurrencyService;
use App\Models\OdinProduct;
use App\Models\OdinCustomer;
use App\Models\OdinOrder;
use App\Models\Txn;
use App\Services\OrderService;

class EbanxController extends Controller
{
    
    /**
     * @var OrderService
     */
    protected $orderService;
    
    /**
     * @var Currency
     */
    protected $currency;
    
    /**
     * EbanxController constructor.
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    /**
     * Send transaction
     * @return type
     */
    public function sendTransaction(EbanxSendTransactionRequest $request) 
    {
        $url = $this->getBaseUrl().'ws/direct';

        $key = Setting::where(['key' => 'ebanx_integration_key'])->first();
        
        if (!$key) {
            logger()->error("ebanx_integration_key parameter not found");            
        }
        
        //check product       
        $product = OdinProduct::where('skus.code', $request->input('sku'))->first();
        if (!$product) {
            logger()->error("Ebanx send transaction: SKU not found", ['sku' => $request->input('sku')]);
            return response()->json(['error' => ['SKU not found']], 402);
        }        
        
        //check prices
        $priceQty = !empty($product->prices[$request->input('quantity')]) ? $product->prices[$request->input('quantity')] : null;
        if ($priceQty['value'] != $request->input('amount_total')) {
            logger()->error("Ebanx send transaction: Prices do not match", ['priceQty' => $priceQty, 'amount_total' => $request->input('amount_total')]);
            return response()->json(['error' => ['Prices do not match']], 402);
        }
        
        $this->currency = CurrencyService::getCurrency();
        
        // customer 
        $customer = $this->saveCustomer($request->all());
        
        // save order
        $order = $this->saveOrder($request->all(), $customer, $product);
        echo '<pre>'; var_dump($customer); echo '</pre>'; exit;
        
        $dataForCurl = [            
            "integration_key" => $key->value,
            "operation" => "request",
            "mode" => "full",
            "payment" => [
                "amount_total" => $request->input('amount_total'),
                "currency_code" => $this->currency->code,
                "name" => $request->input('first_name').' '.$request->input('last_name'),
                "merchant_payment_code" => \Utils::randomString(10),
                "email" => $request->input('email'),
                "birth_date" => $request->input('birth_date'),
                "document" => $request->input('document'),
                "address" => $request->input('address'),
                "street_number" => $request->input('street_number'),
                "city" => $request->input('city'),
                "state" => $request->input('state'),
                "zipcode" => $request->input('zipcode'),
                "country" => $request->input('country'),
                "phone_number" => $request->input('phone'),
                "payment_type_code" => $request->input('payment_type_code'),
                "creditcard" => [
                    "token" => $request->input('token')
                ]
            ]
        ];
        
        
        
        exit;
        try {
            $client = new \GuzzleHttp\Client();
            $request = $client->request('POST', $url, [
                'headers' => [
                    //'Authorization' => "Basic ".base64_encode(env('BLUESNAP_API_KEY').':'.env('BLUESNAP_API_PASS')),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                \GuzzleHttp\RequestOptions::JSON => $dataForCurl
            ]);            
            $response = $request->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse()->getBody()->getContents();
        }
        
        return $response;
    }
    
    private function getBaseUrl()
    {
        $mode = Setting::where(['key' => 'ebanx_mode'])->first();
        
        if (!$mode) {
            logger()->error("ebanx_mode parameter not found");
        }
        
        if ($mode && $mode->value == 'prod') {
            $url = 'https://sandbox.ebanxpay.com/';
        } else {
            $url = 'https://sandbox.ebanxpay.com/';
        }
        return $url;
    }
    
    /**
     * Save customer
     * @param array $request
     * @param type $returnModel
     * @return array
     */
    private function saveCustomer(array $request): OdinCustomer
    {
        $data = [
            'email' => $request['email'],
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'ip' => request()->ip(),
            'phone' => $request['phone'],
            'language' => app()->getLocale(),
            'country' => $request['country'],
            'zip' => $request['zipcode'],
            'state' => $request['state'],
            'city' => $request['city'],
            'street' => $request['address'],
            'street2' => $request['street_number'],            
        ];
        
        $res = $this->orderService->addCustomer($data, true);
        if ($res['success']) {
            return $res['customer'];
        } else {
            abort(404);
        }        
    }
    
    /**
     * Save order
     * @param array $request
     * @param OdinCustomer $customer
     * @param OdinProduct $product
     */
    private function saveOrder(array $request, OdinCustomer $customer, OdinProduct $product)
    {
        $price = (float)$product->prices[$request['quantity']]['value'];
        $warrantyPercent = !empty($request['is_warrantry_checked']) && $product->warranty_percent > 0 ? $product->warranty_percent : 0;
echo '<pre>'; var_dump($product->prices); echo '</pre>'; exit;
        $productForOrder = [
            "sku_code" => $request['sku'],
            "quantity" => (int)$request['quantity'],
            "price" => $price,
            "price_usd" => round($price / $this->currency->usd_rate, 2),
            "warranty_price" => round(($warrantyPercent / 100) * $price, 2),
            "warranty_price_usd" => round(($warrantyPercent / 100) * $price / $this->currency->usd_rate, 2),            
            'price_set' => $product->prices['price_set'],          
        ];
        
        $data = [            
            'status' => OdinOrder::STATUS_NEW,
            'currency' => $this->currency->code,
            'exchange_rate' => $this->currency->usd_rate, // * float
            'total_paid' => $productForOrder['price'],
            'total_price' => $productForOrder['price'] + $productForOrder['warranty_price'],
            'total_price_usd' => round($productForOrder['price'] + $productForOrder['warranty_price'] / $this->currency->usd_rate, 2),
            //'txns_fee_usd' => null, //float, total amount of all txns' fee in USD
            'payment_provider' => 'ebanx',
            'payment_method' => 'card',            
            'customer_email' => $request['email'],
            'customer_first_name' => $request['first_name'],
            'customer_last_name' => $request['last_name'],
            'customer_phone' => $request['phone'],
            'language' => app()->getLocale(),
            'ip' => request()->ip(),
            'shipping_country' => $request['country'],
            'shipping_zip' => $request['zipcode'],
            'shipping_state' => $request['state'],
            'shipping_city' => $request['city'],
            'shipping_street' => $request['address'],
            'shipping_street2' => $request['street_number'],      
            'warehouse_id' => $product->warehouse_id,
            'products' => $productForOrder,
        ];
        
        echo '<pre>'; var_dump($data); echo '</pre>'; exit;
    }
    
}
