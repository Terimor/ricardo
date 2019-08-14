<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use tdanielcox\Bluesnap\Bluesnap;
use tdanielcox\Bluesnap\CardTransaction;
use GuzzleHttp\Client;

class BluesnapController extends Controller
{
    protected $init;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {        
        Bluesnap::init(env('BLUESNAP_API_ENV'), env('BLUESNAP_API_KEY'), env('BLUESNAP_API_PASS'));        
    }
    
    public function createTransaction(Request $request)
    {
        $request->validate([
            'expiration_month' => 'required',
            'expiration_year' => 'required',
            'security_code' => 'required',
            'card_number' => 'required',
        ]);
exit;
        $response = CardTransaction::create([
            'creditCard' => [
                'cardNumber' => $request->input('card_number'),
                'expirationMonth' => $request->input('expiration_month'),
                'expirationYear' => $request->input('expiration_year'),
                'securityCode' => $request->input('security_code')
            ],
            'amount' => 10.00,
            'currency' => 'USD',
            'cardTransactionType' => 'AUTH_CAPTURE',
        ]);
        
        if ($response->failed())
        {
            $error = $response->data;
            // handle error
            echo 'ERROR';
            echo '<pre>'; var_dump($error); echo '</pre>';
            exit;
        }
        $transaction = $response->data;
        
        echo 'SUCCESS';
        echo '<pre>'; var_dump($transaction); echo '</pre>';
    }

    /**
     * Generate token using basic auth
     * @return type
     */
    public function generateToken() 
    {       
        $url = Bluesnap::getBaseUrl().'payment-fields-tokens';
        $client = new \GuzzleHttp\Client();
        $request = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => "Basic ".base64_encode(env('BLUESNAP_API_KEY').':'.env('BLUESNAP_API_PASS')),
            ],
            'form_params' => [
            ]
        ]);
        
        $response = $request->getHeader('Location');
        
        if (!empty($response[0])) {
            $token = basename($response[0]);
        } else {
            logger()->error('Can not generate bluesnap token', ['api_key' => env('BLUESNAP_API_KEY'), 'api_pass' => env('BLUESNAP_API_PASS'), 'url' => $url]);
            $token = null;
        }
        return $token;
    }
    
    /**
     * Send transaction
     * @return type
     */
    public function sendTransaction(Request $request) 
    {
        $url = Bluesnap::getBaseUrl().'transactions';        
        try {
            $client = new \GuzzleHttp\Client();
            $request = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => "Basic ".base64_encode(env('BLUESNAP_API_KEY').':'.env('BLUESNAP_API_PASS')),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                \GuzzleHttp\RequestOptions::JSON => $request->all()
            ]);            
            $response = $request->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse()->getBody()->getContents();
        }
        
        return $response;
    }
    
}
