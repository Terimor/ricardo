<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EmailService;
use StdClass;
use App\Models\Setting;


/*use com\checkout;
use com\checkout\ApiServices;*/

class EmailController extends Controller
{
    
    protected $emailService;
    public static $emailValidStatus = 'deliverable';
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }
    
    /**
     * Validate email using service
     * @param Request $request
     * @return boolean
     */
    public function validateEmailUsingService(Request $request)
    {        
        $apiKey = Setting::getValue('thechecker_api_key', '478132cebcbdb2273989ead9e86313b30cc1e6dd55e29139623ee11bda7c1215');
        
        $email = $request->get('email');
        $isValid = 0;
        if ($email) {            
            // validate email using php
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {                
                $url = "https://api.thechecker.co/v2/verify?email={$email}&api_key={$apiKey}";
                $result = file_get_contents($url);
                $res = json_decode($result);
                if ($res) {
                    if (!empty($res->result) && $res->result == static::$emailValidStatus) {
                        $isValid = 1;
                    } else {
                        $isValid = 0;
                    }
                } else {
                    $isValid = 1;
                    logger()->error("Validate email fail, can't decode {$url}");
                }
            } else {
                $isValid = 0;
            }
        }
        
        return $isValid;        
    }
}
