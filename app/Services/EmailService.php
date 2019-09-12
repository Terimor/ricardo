<?php

namespace App\Services;
use GuzzleHttp\Client;
use App\Models\Setting;
use App\Models\Txn;
use App\Models\OdinOrder;

/**
 * Email Service class
 */
class EmailService
{
    /**
     *
     * @var type 
     */
    protected $apiKey;

    /**
     * 
     */
    public function __construct()
    {
        $apiKey = Setting::getValue('saga_api_access_key');
        $this->apiKey = !empty($apiKey) ? $apiKey : '';
    }
    
    /**
     * Send confirmation email to SAGA service
     * @param type $customer
     * @param type $products
     */
    public function sendConfirmationEmail(Txn $txn) 
    {
        $client = new \GuzzleHttp\Client();
        
        $urlPath = Setting::getValue('saga_api_endpoint');
        $urlPath = !empty($urlPath) ? $urlPath : '';
        
        $url = $urlPath.'?r=odin-api/send-confirmation-email';
        
        // find order by hash
        $order = OdinOrder::where('txns.hash', $txn->hash)->first();
        echo '<pre>'; var_dump($order->toArray()); echo '</pre>'; exit;
        if (!$order->customer_email && !$order->products) {
            logger()->error('Fail confirmation email on order', ['order' => $order->toArray()]);
            abort(404);
        }
        echo '<pre>'; var_dump($order); echo '</pre>'; exit;
        
        $request = $client->request('POST', $url, [
            'headers' => [
                'api-token' => $this->apiKey,
            ],
            'form_params' => [
                'language' => app()->getLocale(),
                'customer_number' => $customer->number,
                'customer_name' => $customer->name,
                'customer_address' => $customer->address,
                'customer_email' => $customer->email,
                'products' => $products
            ]
        ]);
        
        //$response = $request->getBody()->getContents();
    }
    
    /**
     * Send satisfaction email to SAGA service     
     * @param type $customer
     */
    public function sendSatisfactionEmail($customer, $surveyLink)
    {
        $client = new \GuzzleHttp\Client();
        
        $urlPath = Setting::getValue('saga_api_endpoint');
        $urlPath = !empty($urlPath) ? $urlPath : '';
        
        $url = $urlPath.'?r=odin-api/send-satisfaction-email';
        $request = $client->request('POST', $url, [
            'headers' => [
                'api-token' => $this->apiKey,
            ],
            'form_params' => [
                'language' => app()->getLocale(),
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'survey_link' => $surveyLink
            ]
        ]);
        
        //$response = $request->getBody()->getContents();       
    }
    
    

}