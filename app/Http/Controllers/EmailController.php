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
    public function validateEmail(Request $request)
    {                        
        $email = $request->get('email');
        $res = false;
        if ($email) {
            $res = $this->emailService->validateEmailWithIPQS($email);            
        }
        
        return response()->json($res);
    }
}
