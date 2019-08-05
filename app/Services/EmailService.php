<?php

namespace App\Services;
use GuzzleHttp\Client;
use App\Models\Setting;

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

    public function __construct()
    {
        $apiKey = Setting::where(['key' => 'saga_api_access_key'])->first();
        $this->apiKey = !empty($apiKey->value) ? $apiKey->value : '';
    }
    
    /**
     * Send confirmation email to SAGA service
     * @param type $customer
     * @param type $products
     */
    public function sendConfirmationEmail($customer, $products) 
    {
        $client = new \GuzzleHttp\Client();
        
        $urlPath = Setting::where(['key' => 'saga_api_endpoint'])->first();
        $urlPath = !empty($urlPath->value) ? $urlPath->value : '';
        
        $url = $urlPath.'index.php?r=odin-api/send-confirmation-email';
        
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
        
        $urlPath = Setting::where(['key' => 'saga_api_endpoint'])->first();
        $urlPath = !empty($urlPath->value) ? $urlPath->value : '';
        
        $url = $urlPath.'index.php?r=odin-api/send-satisfaction-email';
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