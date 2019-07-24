<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use Checkout\CheckoutApi;
use Checkout\Models\Tokens\Card;
use Checkout\Models\Payments\TokenSource;
use Checkout\Models\Payments\Payment;


/*use com\checkout;
use com\checkout\ApiServices;*/

class PaymentsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }
    
    public function testBluesnap()
    {
        \tdanielcox\Bluesnap\Bluesnap::init(env('BLUESNAP_API_ENV'), env('BLUESNAP_API_KEY'), env('BLUESNAP_API_PASS'));
        
        $response = \tdanielcox\Bluesnap\CardTransaction::create([
            'creditCard' => [
                'cardNumber' => '4263982640269299',
                'expirationMonth' => '02',
                'expirationYear' => '2020',
                'securityCode' => '837'
            ],
            'amount' => 10.00,
            'currency' => 'USD',
            'recurringTransaction' => 'ECOMMERCE',
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
        
        echo '<pre>'; var_dump($transaction); echo '</pre>';
        exit;
    }
    
    public function testCheckoutCom()
    {
        // Set the secret key
        $secretKey = env('CHECKOUTCOM_API_SECRET');

        // Initialize the Checkout API
        $checkout = new CheckoutApi($secretKey);

        // Create a payment method instance with card details
        $method = new TokenSource('tok_key_goes_here');

        // Prepare the payment parameters
        $payment = new Payment($method, 'GBP');
        $payment->amount = 1000; // = 10.00

        // Send the request and retrieve the response
        $response = $checkout->payments()->request($payment);

        /*$apiClient = new checkout\ApiClient($secretKey);
        
        $charge = $apiClient->chargeService();

        //create an instance of a CardChargeCreate model
        $cardChargePayload = new Charges\RequestModels\CardChargeCreate();
        //initializing model to generate payload
        $baseCardCreateObject = new Cards\RequestModels\BaseCardCreate();

        $billingDetails = new SharedModels\Address();
        $phone = new  SharedModels\Phone();

        $phone->setNumber("203 583 44 55");
        $phone->setCountryCode("44"); 

        $billingDetails->setAddressLine1('1 Glading Fields"');
        $billingDetails->setPostcode('N16 2BR');
        $billingDetails->setCountry('GB');
        $billingDetails->setCity('London');
        $billingDetails->setPhone($phone);

        $baseCardCreateObject->setNumber('4242424242424242');
        $baseCardCreateObject->setName('Test Name');
        $baseCardCreateObject->setExpiryMonth('06');
        $baseCardCreateObject->setExpiryYear('2022');
        $baseCardCreateObject->setCvv('100');
        //$baseCardCreateObject->setBillingDetails($billingDetails);

        $cardChargePayload->setEmail('lorderetik@gmail.com');
        $cardChargePayload->setAutoCapture('N');
        $cardChargePayload->setAutoCaptime('0');
        $cardChargePayload->setValue('100');
        $cardChargePayload->setCurrency('usd');
        $cardChargePayload->setTrackId('Demo-0001');
        $cardChargePayload->setBaseCardCreate($baseCardCreateObject);

        try {
            $ChargeResponse = $charge->chargeWithCard($cardChargePayload);
            echo '<pre>'; var_dump($ChargeResponse); echo '</pre>';
        } catch (com\checkout\helpers\ApiHttpClientCustomException $e) {
            echo 'Caught exception Message: ',  $e->getErrorMessage(), "\n";
            echo 'Caught exception Error Code: ',  $e->getErrorCode(), "\n";
            echo 'Caught exception Event id: ',  $e->getEventId(), "\n";
        }*/
    }
    

}
