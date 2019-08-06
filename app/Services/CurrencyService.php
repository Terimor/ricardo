<?php

namespace App\Services;
use App\Models\Currency;

/**
 * Currency Service class
 */
class CurrencyService
{
    /**
     * Currency codes round to 0,95
     * @var type 
     */
    public static $roundTo0_95 = ['CHF'];
    
    /**
     * Up to next 500
     * @var type 
     */
    public static $upToNext500 = ['KRW'];
    
    /**
     * Up to next 10
     * @var type 
     */
    public static $upToNext10 = ['JPY'];
    
    public function __construct()
    {
    }
    
    /**
     * 
     * @param type $price
     * @param type $toCurrency
     * @return type
     */
    public static function getLocalPriceFromUsd($price, $toCurrency, $locale)
    {
        $toCurrency = strtoupper($toCurrency);        
        $currency = Currency::whereCode($toCurrency)->first();
        
        if (!$currency) {
            return 0;
        }
        
        //get fraction digits and locale
        $localeString = strtolower($locale).'-'.strtoupper($locale);
        $numberFormatter = new \NumberFormatter($localeString, \NumberFormatter::CURRENCY); 
        
        $fractionDigits = $numberFormatter->getAttribute(\NumberFormatter::MAX_FRACTION_DIGITS);
        
        $exchangedPrice = $price * $currency->usd_rate;
        
        if (in_array($toCurrency, static::$upToNext500)) {
            $exchangedPrice = ceil($exchangedPrice);            
            $exchangedPrice = $exchangedPrice/100;
            $exchangedPrice = (string) $exchangedPrice;
            $digits = strlen((int)$exchangedPrice);
            
            // if digits[-1] >= 5 than we need to add 1 to 1000 and add next 500
            if ($exchangedPrice[$digits-1] >= 5) {
                $exchangedPrice[$digits-1] = 9;
                $exchangedPrice = (int) $exchangedPrice + 1 + 5;
            } else {
                $exchangedPrice[$digits-1] = 5;
            }
            
            $exchangedPrice = (int)$exchangedPrice;
            $exchangedPrice = $exchangedPrice * 100;
             
            return $exchangedPrice;
        }
        
        if (in_array($toCurrency, static::$upToNext10)) {
            $exchangedPrice = (int)$exchangedPrice;
            $exchangedPrice = (string) $exchangedPrice;
            $digits = strlen((int)$exchangedPrice);
            // if digits[-1 and -2] != 10 than we set 99 + 1 and add next +10
            if ($exchangedPrice[$digits-1] != 0 && $exchangedPrice[$digits-2] != 1) {
                $exchangedPrice[$digits-1] = 9;
                $exchangedPrice[$digits-2] = 9;

                $exchangedPrice = $exchangedPrice + 1 + 10;
            }
            $exchangedPrice = (int)$exchangedPrice;
             
            return $exchangedPrice;
        }
        
        if ($fractionDigits == 0) {
            $exchangedPrice = (int) $exchangedPrice;
        }
        
        $digits = strlen((int)$exchangedPrice);
        $exchangedPrice = (string) $exchangedPrice;

        if ($digits >= 4) {            
            $roundedPriceString = '';
            for ($i = 0; $i < $digits; $i++) {
                //first numeral always the same
                if ($i == 0) {
                    $roundedPriceString .= $exchangedPrice[$i];
                } elseif ($i == 1) {
                    //second numeral stand 9 if >= 5 else 4
                    if ($exchangedPrice[$i] >= 5) {
                        $roundedPriceString .= '9';
                    } else {
                        $roundedPriceString .= '4';
                    }
                } else {
                    // next numerals always 9
                    $roundedPriceString .= '9';
                }
            }
            $exchangedPrice = $roundedPriceString;
        } else if ($digits == 3) {
            //if 3 digits always set 9 to the last numeral
            $exchangedPrice[2] = '9';
        } else if ($digits == 2) {
            //if 2 digits and last numeral >= 5 always set 9 than 4
            if ($exchangedPrice[1] >= 5) {
                $exchangedPrice[1] = '9';
            } else {
                $exchangedPrice[1] = '4';
            }
        }

        $exchangedPrice = (int) $exchangedPrice;
        
        if ($fractionDigits > 0) {
            $exchangedPrice += 1;
            $exchangedPrice -= 0.01;

            if (in_array($toCurrency, static::$roundTo0_95)) {
                $exchangedPrice -= 0.04;

            }
        }
        
        return $exchangedPrice;
    }
    
    
}