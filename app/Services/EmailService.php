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
    
    public static $thecheckerEmailValidStatus = 'deliverable';
    
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
     * Validate email with thecheker.io
     * @param string $email
     * @return bool
     */
    public function validateEmailWithThechecker(string $email) : bool
    {        
        $apiKey = Setting::getValue('thechecker_api_key');
        // validate email using php
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {                
            $url = "https://api.thechecker.co/v2/verify?email={$email}&api_key={$apiKey}";
            $result = file_get_contents($url);
            $res = json_decode($result);
            if ($res) {
                if (!empty($res->result) && $res->result == static::$thecheckerEmailValidStatus) {
                    $isValid = true;
                } else {
                    $isValid = false;
                }
            } else {
                $isValid = true;
                logger()->error("Validate email fail, can't decode {$url}");
            }
        } else {
            $isValid = false;
        }        
        return $isValid;
    }
    

}