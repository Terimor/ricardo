<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Setting;
use App\Http\Requests\EbanxSendTransactionRequest;
use App\Services\CurrencyService;

class EbanxController extends Controller
{
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
        
        $dataForCurl = [            
            "integration_key" => $key->value,
            "operation" => "request",
            "mode" => "full",
            "payment" => [
                "amount_total" => $request->input('amount_total'),
                "currency_code" => CurrencyService::getCurrency()->code,
                "name" => $request->input('name'),
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
                "phone_number" => $request->input('phone_number'),
                "payment_type_code" => $request->input('payment_type_code'),
                "creditcard" => [
                    "token" => $request->input('token')
                ]
            ]
        ];
        //echo '<pre>'; var_dump($dataForCurl); echo '</pre>'; exit;
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
    
}
