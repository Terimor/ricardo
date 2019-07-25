<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use tdanielcox\Bluesnap\Bluesnap;
use tdanielcox\Bluesnap\CardTransaction;

class BluesnapController extends Controller
{
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

        $response = CardTransaction::create([
            'creditCard' => [
                'cardNumber' => $request->input('card_number'),
                'expirationMonth' => $request->input('expiration_month'),
                'expirationYear' => $request->input('expiration_year'),
                'securityCode' => $request->input('security_code')
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
        
        echo 'SUCCESS';
        echo '<pre>'; var_dump($transaction); echo '</pre>';
    }    
}
