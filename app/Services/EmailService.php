<?php

namespace App\Services;
use GuzzleHttp\Client;
use App\Models\Setting;
use App\Models\OdinOrder;
use App\Models\OdinCustomer;

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
    public function sendConfirmationEmail(OdinOrder $order, $customer = null, $isMain = true) 
    {
        $client = new \GuzzleHttp\Client();
        
        $urlPath = Setting::getValue('saga_api_endpoint');
        $urlPath = !empty($urlPath) ? $urlPath : '';
        
        $url = $urlPath.'?r=odin-api/send-confirmation-email';

        if (!$order || !$order->customer_email || !$order->products) {
            logger()->error('Fail confirmation email on order', ['order' => $order->attributesToArray()]);
            abort(404);
        }
        
        // get customer by email
        if (!$customer) {
            $customer = OdinCustomer::where('email', $order->customer_email)->first();            
        }
        
        // address
        $address = $this->prepareOrderAddress($order);
        
        $products = [];
        // products array
        foreach ($order->products as $product) {
            if ($isMain) {
                if (!empty($product['is_main']) && $product['is_main']) {
                    $products[] = $product;
                }
            } else {
                if (!empty($product['is_upsells']) && $product['is_upsells']) {
                    $products[] = $product;
                }
            }
        }
        
        $request = $client->request('POST', $url, [
            'headers' => [
                'api-token' => $this->apiKey,
            ],
            'form_params' => [
                'language' => app()->getLocale(),
                'customer_number' => $customer->number,
                'customer_name' => trim($customer->first_name.' '.$customer->last_name),
                'customer_address' => $address,
                'customer_email' => $customer->email,
                'products' => $products,
                'currency' => $order->currency                
            ]
        ]);
        
        $response = $request->getBody()->getContents();        
        echo '<pre>'; var_dump($response); echo '</pre>'; exit;
    }
    
    /**
     * Send satisfaction email to SAGA service     
     * @param type $customer
     */
    public function sendSatisfactionEmail(OdinOrder $order, $customer = null, $surveyLink)
    {
        $client = new \GuzzleHttp\Client();
        
        $urlPath = Setting::getValue('saga_api_endpoint');
        $urlPath = !empty($urlPath) ? $urlPath : '';
        
        $url = $urlPath.'?r=odin-api/send-satisfaction-email';
        
        if (!$order || !$order->customer_email || !$order->products) {
            logger()->error('Fail confirmation email on order', ['order' => $order->attributesToArray()]);
            abort(404);
        }
        
        // get customer by email
        if (!$customer) {
            $customer = OdinCustomer::where('email', $order->customer_email)->first();            
        }
        
        // address
        $address = $this->prepareOrderAddress($order);
        
        $products = [];
        // products array
        foreach ($order->products as $product) {
            if (!empty($product['is_main']) && $product['is_main']) {
                $products[] = $product;
                break;
            }
        }
        
        
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
    
    /**
     * Prepare order address
     * @param OdinOrder $order
     */
    public function prepareOrderAddress(OdinOrder $order) : string
    {
        $address = ' ';
        
        if (!empty($order['shipping_zipcode'])) {
            $address .= $order['shipping_zipcode'].' ';
        }
        
        if (!empty($order['shipping_city'])) {
            $address .= $order['shipping_city'].' ';
        }
        
        if (!empty($order['shipping_street'])) {
            $address .= '- '.$order['shipping_street'].' ';
        }
        
        if (!empty($order['shipping_street2'])) {
            $address .= $order['shipping_street2'].' ';
        }
        
        if (!empty($order['shipping_apt'])) {
            $address .= ', '.$order['shipping_apt'].' ';
        }
        
        return trim($address);
    }
    
    

}