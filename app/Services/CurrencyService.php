<?php

namespace App\Services;
use App\Models\Currency;

/**
 * Currency Service class
 */
class CurrencyService
{
    /**
     * Currency codes round to 95
     * @var type 
     */
    protected $roundTo95 = ['CHF'];
    
    public function __construct()
    {
    }
    
    /**
     * 
     * @param type $price
     * @param type $toCurrency
     * @return type
     */
    public function exchangePrice($price, $toCurrency)
    {
        $toCurrency = strtoupper($toCurrency);        
        $currency = Currency::whereCode($toCurrency)->first();
        
        if (!$currency) {
            return 0;
        }
        
        $exchangedPrice = ceil($price * $currency->usd_rate);
        $exchangedPrice -= 0.01;
        
        if (in_array($toCurrency, $this->roundTo95)) {
            $exchangedPrice -= 0.04;
        }
        
        return $exchangedPrice;
    }
    
    
}