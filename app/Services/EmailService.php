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
    public static $ipqsLowDeliverability = 'low';
    public static $NA = 'N/A';
    
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
    
    /**
     * Validate Email with ipqualityscore.com
     * @param string $email
     * @return type
     */
    public function validateEmailWithIPQS(string $email)
    {
        $apiKey = Setting::getValue('ipqs_private_api_key');
        $block = false; $suggest = ''; $warning = false;
        if ($email) {
            $url =  "https://www.ipqualityscore.com/api/json/email/{$apiKey}/{$email}";
            $timeOut = stream_context_create(
                array('http'=>
                    array(
                        'timeout' => 5,  
                    )
                )
            );
            for ($i=1; $i<=3; $i++) {
                try {
                    $result = file_get_contents($url, false, $timeOut);                

                    $res = json_decode($result);
                    if ($res) {                        
                        // block if recent_abuse, leaked or disposable
                        if ((isset($res->overall_score) && $res->overall_score == 0) || !empty($res->recent_abuse) || !empty($res->leaked) || !empty($res->disposable)) {
                            logger()->error("Blocked email", ['res' => $res, 'email' => $email]);
                            $block = false;
                        }

                        // check warning 
                        if (!empty($res->timed_out) || $res->deliverability == static::$ipqsLowDeliverability) {
                            $warning = true;
                        }

                        if (!empty($res->suggested_domain) && $res->suggested_domain != static::$NA) {
                            $domain = explode('@', $email)[1];
                            $suggest = str_replace($domain, $res->suggested_domain, $email);
                        }

                        break;
                    }
                } catch (\Exception $ex) {                    
                    logger()->error("Validate email IPQS connection error", ['code' => $ex->getCode(), 'message' => $ex->getMessage()]);
                }
            }
        } else {
            $block = true;
        }

        return [
            'block' => $block,
            'warning' => $warning,
            'suggest' => $suggest
        ];
    }
    

}