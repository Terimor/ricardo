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
    
    
    public function testConfirmationEmail()
    {
        exit;
        $customer = new StdClass();
        $customer->number = '25 3254 32432 54';
        $customer->name = 'Test name';
        $customer->address = 'Narva 7 asdasd - Tallinn 10117 - EE';
        $customer->email = 'lorderetik@gmail.com';
        
        $products = [];
        $products[] = [
            'name' => 'product HELLO',
            'quantity' => '1',
            'amount' => '45.50',  
        ];
        
        $products[] = [
            'name' => 'product KITTY',
            'quantity' => '5',
            'amount' => '22.60',  
        ];
        
        $this->emailService->sendConfirmationEmail($customer, $products);
    }
       
    public function testSatisfactionEmail()
    {
        exit;
        $customer = new StdClass();        
        $customer->name = 'Test name';
        $customer->email = 'lorderetiks@gmail.com';

        $orderId = 'test-TO-201906-a458fcad-55ba-4e17-ba1d-d41bf9b77239';
        $product = 'EchoBeat 07';
        $domain = 'https://www.echobeat.pro';
        
        $surveyLink = Setting::getValue('survey_link_template');
        $surveyLink = !empty($surveyLink) ? $surveyLink : '';
        
        $surveyLink = str_replace('#PRODUCT_NAME#', $product, $surveyLink);
        $surveyLink = str_replace('#DOMAIN#', $domain, $surveyLink);
        $surveyLink = str_replace('#ORDER_ID#', $orderId, $surveyLink);

        $this->emailService->sendSatisfactionEmail($customer, $surveyLink);
    }
    
    
}
