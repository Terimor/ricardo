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
     * Get local price from USD
     * @param float $price
     * @param string $toCurrency
     * @param string $countryCode
     * @param type $ip
     * @return float
     */
    public static function getLocalPriceFromUsd(float $price, Currency $currency = null, string $countryCode = null, $ip = null) : array
    {
        if (!$currency) {
            $currency = self::getCurrency($currencyCode, $countryCode);
        }
        $currencyCode = $currency->code;
        $countryCode = $currency->countryCode;
 
        //get fraction digits and locale string        
        $localeString = \Utils::getCultureCode($ip, $countryCode);
        $numberFormatter = new \NumberFormatter($localeString, \NumberFormatter::CURRENCY);
        //$start = microtime(true);
        $fractionDigits = $numberFormatter->getAttribute(\NumberFormatter::MAX_FRACTION_DIGITS);
        //echo 'Script time: '.(microtime(true) - $start).' sec.';
        
        $exchangedPrice = $price * $currency->usd_rate;
        
        if (in_array($currencyCode, static::$upToNext500)) {            
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
                        
            return [
                'price' => $exchangedPrice,
                'price_text' =>  $numberFormatter->formatCurrency($exchangedPrice, $currencyCode),
                'code' => $currencyCode,
                'exchange_rate' => $currency->usd_rate,
            ];
        }
        
        if (in_array($currencyCode, static::$upToNext10)) {
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
             
            return [
                'price' => $exchangedPrice,
                'price_text' =>  $numberFormatter->formatCurrency($exchangedPrice, $currencyCode),
                'code' => $currencyCode,
                'exchange_rate' => $currency->usd_rate,
            ];
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

            if (in_array($currencyCode, static::$roundTo0_95)) {
                $exchangedPrice -= 0.04;

            }
        }
        
        return [
            'price' => $exchangedPrice,
            'price_text' =>  $numberFormatter->formatCurrency($exchangedPrice, $currencyCode),
            'code' => $currencyCode,
            'exchange_rate' => $currency->usd_rate,
        ];
    }
    
    /**
     * Get currency array
     * @param string $countryCode
     * @return Currency
     */
    public static function getCurrency(string $currencyCode = null, string $countryCode = null) : Currency
    {
        if (request()->has('cur')) {
            $currencyCode = request()->input('cur');          
        } else {
            
            if (!$currencyCode) {            
                if (!$countryCode) {
                    $countryCode = strtoupper(\Utils::getLocationCountryCode());
                }
                
                $localeString = \Utils::getCultureCode(null, $countryCode);            
                $numberFormatter = new \NumberFormatter($localeString, \NumberFormatter::CURRENCY); 
                $currencyCode = $numberFormatter->getTextAttribute(\NumberFormatter::CURRENCY_CODE);
                
            }
        }

        $currency = Currency::whereCode($currencyCode)->first();
        
        if (!empty($currency->countries)){
            if (!in_array(strtolower($countryCode), $currency->countries)) {                
                $countryCode = $currency->countries[0];
            }
        } else {            
            logger()->error("Can't find currency country", ['currency' => $currency ? $currency->toArray() : '', 'currencyCode' => $currencyCode]);
            //try to find in currency countries
            if ($countryCode) {                
                $currency = Currency::where(['countries' => strtolower($countryCode)])->first();
                
                if(!$currency) {
                    $currencyCode = 'USD';
                }
            } else {
                $currencyCode = 'USD';
            }
            $currency = Currency::whereCode($currencyCode)->first();
            $countryCode = !empty($currency->countries[0]) ? $currency->countries[0] : 'US';
        }
        
        $currency->countryCode = $countryCode;
        
        return $currency;
    }
    
}