<?php

namespace App\Services;
use App\Models\Currency;
use App\Models\OdinProduct;
use Cache;

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

    /**
     * Use currency symbol from database
     * @var type
     */
    public static $useDBSymbol = [
        'HRK' => 'HRK',
        'AUD' => '$',
        'NZD' => '$',
        'SGD' => '$',
        'HKD' => 'HK$',
        'TWD' => '$',       
        'BSD' => '$',
        'BBD' => '$',
        'BZD' => '$',
        'BMD' => '$',
        'BOB' => '$',
        'BND' => '$',
        'CAD' => '$',
        'KYD' => '$',
        'CLP' => '$',
        'COP' => '$',
        'XCD' => '$',
        'SVC' => '$',
        'FJD' => '$',        
        'LRD' => '$',
        'MXN' => '$',
        'NAD' => '$',        
        'SBD' => '$',
        'SRD' => '$',
    ];

    /**
     * Remove decimals
     * @var type
     */
    public static $noDecimals = ['HRK'];

    /**
     * 0 at the and of price
     * @var type
     */
    public static $zeroAtTheEnd = [
        'IDR',
        'HKD',
        'TWD',
        'HUF',
        'SEK',
        'DKK',
        'NOK',
        'CNY',
        'INR',
        'PHP',
        'ZAR',
        'THB',
        'ISK',
        'CZK',
        'ARS',
        'CLP',
        'COP',
        'CRC',
        'DOP',
        'HNL',
        'MXP',
        'NIO',
        'PYG',
        'SVC',
        'TTD',
        'UYU',
        'VEF',
        'BDT',
        'LKR',
        'MVR',
        'PKR',
        'IQD',
        'CZK',
        'DKK',
        'EEK',
        'HUF',
        'ISK',
        'MDL',
        'MKD',
        'NOK',
        'RSD',
        'RUB',
        'SEK',
        'SKK',
        'TRY',
        'UAH',
        'KZT',
        'LBP',
        'UZS',
        'YER',
        'BWP',
        'DZD',
        'EGP',
        'KES',
        'MAD',
        'MUR',
        'NAD',
        'NGN',
        'SCR',
        'SLL',
        'TZS',
        'UGX',
        'XOF',
        'ZAR',
        'ZMK',
        'AOA',
        'GNF',
        'VND',
        'SYP',
        'XAF',
        'JMD',
        'KGS',
        'XPF',
        'KHR',
        'SOS',
        'MZN',
        'LAK',
        'GYD',
        'MMK',
        'MGA',
        'MWK',
        'NPR',
        'DJF',
        'BIF',
        'CDF',
        'GMD',
        'MNT',
        'RWF',
        'ALL',
        'BTN',
        'KMF',
        'CVE',
        'AMD',
        'LRD',
        'MXN',
        'HTG',
        'IRR',
        'VUV',
        'AFN',
        'KPW',
        'SDG',
        'STD',
        'MRO'
    ];

    /**
     * Get local price from USD
     * @param float $price
     * @param string $currency
     * @param string $countryCode
     * @param type $ip
     * @return float
     */
    public static function getLocalPriceFromUsd(float $price, Currency $currency) : array
    {
        if (!$currency) {
            $currency = self::getCurrency();
        }
        $currencyCode = $currency->code;
        $countryCode = $currency->countryCode;

        //get fraction digits and locale string
        $localeString = \Utils::getCultureCode(null, $countryCode);

        $numberFormatter = new \NumberFormatter($localeString, \NumberFormatter::CURRENCY);
        $fractionDigits = $numberFormatter->getAttribute(\NumberFormatter::MAX_FRACTION_DIGITS);
        // if price < min set minimum price = min
        if ($price < OdinProduct::MIN_PRICE) {
            logger()->error("Price < ".OdinProduct::MIN_PRICE, ['price' => $price]);
            $price = OdinProduct::MIN_PRICE;
        }
        $exchangedPrice = $price * (!empty($currency->price_rate) ? $currency->price_rate : $currency->usd_rate);

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

            $exchangedPrice = (int)$exchangedPrice * 100;

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

        // when fraction digits = 0, cut decimals
        if ($fractionDigits == 0) {
            $exchangedPrice = (int)$exchangedPrice;
        }

        $digits = strlen((int)$exchangedPrice);

        $exchangedPrice = static::mainRounding($digits, $exchangedPrice);

        if ($fractionDigits > 0) {
            $exchangedPrice += 1;
            $exchangedPrice -= 0.01;

            if (in_array($currencyCode, static::$roundTo0_95)) {
                $exchangedPrice -= 0.04;

            }
        }

        if (in_array($currencyCode, static::$noDecimals)) {
            $exchangedPrice = (int)$exchangedPrice;
            $numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 0);
        }


        if (in_array($currencyCode, static::$zeroAtTheEnd)) {
            $exchangedPrice = static::zeroAtTheEndRounding($digits, $exchangedPrice, $numberFormatter);
        }

        // check decimals for logging
        if (!in_array($currencyCode, static::$zeroAtTheEnd) && $digits >=5) {
            logger()->error("Price {$exchangedPrice} has {$digits} digits for currency {$currencyCode}");
        }

        return [
            'price' => $exchangedPrice,
            'price_text' =>  CurrencyService::formatCurrency($numberFormatter, $exchangedPrice, $currency),
            'code' => $currencyCode,
            'exchange_rate' => $currency->usd_rate,
        ];
    }

    /**
     * Rounding to 90 by rules
     * @param type $digits
     * @param type $exchangedPrice
     * @return type
     */
    private static function zeroAtTheEndRounding(int $digits, $exchangedPrice, $numberFormatter): int
    {
        $exchangedPrice = (int)$exchangedPrice;
        $oldPrice = $exchangedPrice;
        $fractionDigits = 0;
        $numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $fractionDigits);

        // rules by digits
        if ($digits == 3) {
            $exchangedPrice = $exchangedPrice / 10;
            $exchangedPrice = (int)$exchangedPrice * 10 + 9;

            if ($exchangedPrice <= $oldPrice) {
                $exchangedPrice += 10;
            }

        } else if ($digits == 4 || $digits == 5) {
            $exchangedPrice = $exchangedPrice / 100;
            $exchangedPrice = (int)$exchangedPrice * 100 + 90;

            if ($exchangedPrice <= $oldPrice) {
                if ($digits == 4) {
                    $exchangedPrice += 100;
                } else {
                    $exchangedPrice += 1000;
                }
            }
        } else if ($digits == 6) {
            $exchangedPrice = $exchangedPrice / 1000;
            $exchangedPrice = (int)$exchangedPrice * 1000 + 990;
            if ($exchangedPrice <= $oldPrice) {
                $exchangedPrice += 1000;
            }
        } else if ($digits > 6) {
            $exchangedPrice = $exchangedPrice / 10000;
            $exchangedPrice = (int)$exchangedPrice * 10000 + 9900;

            if ($exchangedPrice <= $oldPrice) {
                $exchangedPrice += 10000;
            }

        }

        return (int)$exchangedPrice;
    }

    /**
     * Function for main rounding by rules
     * @param int $digits
     * @param type $exchangedPrice
     */
    private static function mainRounding(int $digits, $exchangedPrice):int
    {
        $exchangedPrice = (string) $exchangedPrice;

        if ($digits > 3) {
            $roundedPriceString = '';
            for ($i = 0; $i < $digits; $i++) {
                // check 2 last digits
                if ($i == ($digits-2)) {
                    // digit set 9 if >= 5 -> else set 4
                    if ($exchangedPrice[$i] >= 5) {
                        $roundedPriceString .= '9';
                    } else {
                        $roundedPriceString .= '4';
                    }
                } else if ($i == ($digits-1)) {
                    // last digit always 9
                    $roundedPriceString .= '9';
                } else {
                    $roundedPriceString .= $exchangedPrice[$i];
                }
            }
            $exchangedPrice = $roundedPriceString;
        } else if ($digits == 2 || $digits == 3) {
            //if 2 digits and last numeral >= 5 always set 9 than 4
            if ($exchangedPrice[$digits-1] >= 5) {
                $exchangedPrice[$digits-1] = '9';
            } else {
                $exchangedPrice[$digits-1] = '4';
            }
        }

        return (int)$exchangedPrice;
    }

    /**
     * Returns old price by item price
     * @param float $itemPrice
     * @param int $quantity
     * @return float
     */
    public static function getOldPrice(float $itemPrice, int $quantity): float
    {
		$oldPrice = $itemPrice * $quantity * 2;
		return $oldPrice;
    }

    /**
     * Returns single installment price
     * @param float $price
     * @param int $installments
     * @return float
     */
    public static function getInstallmentPrice(float $price, int $installments): float {
		$installmentPrice = floor($price * 100 / $installments) / 100;
		return $installmentPrice;
    }

    /**
     * Returns discount percent
     * @param float $priceOld
     * @param float $priceNow
     * @return float
     */
    public static function getDiscountPercent(float $priceOld, float $priceNow): float {
		$percent = round(($priceOld - $priceNow) / $priceOld * 100);
		return $percent;
    }

    /**
     * Returns currency model
     * @param string $currencyCode
     * @param string $countryCode
     * @return Currency
     */
    public static function getCurrency(string $currencyCode = null, string $countryCode = null) : Currency
    {
        $currency = null;
        if ($currencyCode) {
            $currency = Currency::whereCode($currencyCode)->where('status', 'active')->first();
        } else {
            if (request()->get('cur')) {
                $currencyCode = request()->get('cur');
            }
        }

        // if we still haven't currency get it by country
        if (!$currencyCode) {
            if (!$countryCode) {
                $countryCode = strtoupper(\Utils::getLocationCountryCode());
            }

            $localeString = \Utils::getCultureCode(null, $countryCode);
            $numberFormatter = new \NumberFormatter($localeString, \NumberFormatter::CURRENCY);
            $currencyCode = $numberFormatter->getTextAttribute(\NumberFormatter::CURRENCY_CODE);
        }

        // if we still haven't currency get first active currency
        if (!$currency) {
            $currency = Currency::whereCode($currencyCode)->where('status', 'active')->first();
        }

        // check and compare countries in currency
        if (!empty($currency->countries)){
            if (!in_array(strtolower($countryCode), $currency->countries)) {
                $countryCode = $currency->countries[0];
            }
        } else {
            //try to find in currency countries
            if ($countryCode) {
                $currency = Currency::where(['countries' => strtolower($countryCode)])->where('status', 'active')->first();

                if(!$currency) {
                    logger()->error("Can't find currency country", ['currencyCode' => $currencyCode, 'countryCode' => $countryCode]);
                    $currencyCode = 'USD';
                }
            } else {
                $currencyCode = 'USD';
            }

            // default USD currency
            $currency = Currency::whereCode($currencyCode)->first();
            $countryCode = !empty($currency->countries[0]) ? $currency->countries[0] : 'US';
        }

        // if still no currency, then can't find USD try again ...
        if (!$currency) {
            $currency = Currency::where(['code' => 'USD'])->first();
        }
        if (!$currency) {
            logger()->error("Urgent: Can't find currency", ['currencyCode' => $currencyCode, 'countryCode' => $countryCode]);
        }

        $currency->countryCode = !empty($countryCode) ? $countryCode : 'US';

        if (empty($localeString)) {
            $localeString = \Utils::getCultureCode(null, $currency->countryCode);
        }

        $currency->localeString = $localeString;

        return $currency;
    }

	/**
	 * Calculate warranty price
	 * @param float $warrantyPercent
	 * @param float $price
	 */
	public static function calculateWarrantyPrice(float $warrantyPercent, float $price): float
	{
		return floor(($warrantyPercent / 100) * $price * 100)/100;
	}

	/**
	 * Get local text value
	 * @param float $price
	 * @param type $currency
	 */
	public static function getLocalTextValue(float $price, Currency $currency = null) : string
	{
		if (!$currency) {
			$currency = self::getCurrency();
		}

        $countryCode = !empty($currency->countryCode) ? $currency->countryCode : (!empty($currency->countries[0]) ? $currency->countries[0] : 'us');

        //get fraction digits and locale string
        $localeString = \Utils::getCultureCode(null, $countryCode);
        $numberFormatter = new \NumberFormatter($localeString, \NumberFormatter::CURRENCY);

		return CurrencyService::formatCurrency($numberFormatter, $price, $currency);
	}

    /**
     * Returns rounded value depending on currency rules
     * @param float $value
     * @param string $currencyCode
     * @return float
     */
    public static function roundValueByCurrencyRules(float $value, string $currencyCode): float
    {
        // cache culture code
        $localeString = self::getCultureCodeByCurrency($currencyCode);

        $numberFormatter = new \NumberFormatter($localeString, \NumberFormatter::CURRENCY);
        // parse value
        $roundedValue = $numberFormatter->parseCurrency($numberFormatter->formatCurrency($value, $currencyCode), $currencyCode);

        return $roundedValue;
    }
    /**
     * Return culture code by currency
     * @param type $currencyCode
     * @return type
     */
    public static function getCultureCodeByCurrency($currencyCode)
    {
        // cache culture code
        $currencyCultureCodes = Cache::get('CurrencyCultureCode');
        if (empty($currencyCultureCodes[$currencyCode])) {
            $currencyCultureCodes = static::cacheCurrencyCultureCode();
        }
        return !empty($currencyCultureCodes[$currencyCode]) ? $currencyCultureCodes[$currencyCode] : 'en-US';
    }

    /**
     * Cache and return currency culture code array, for example US => en-US
     * @return string
     */
    public static function cacheCurrencyCultureCode()
    {
        $currencies = Currency::where(['status' => 'status'])->get();
        $currencyCultureCodes = [];
        // check countries
        foreach ($currencies as $currency) {
            if (!empty($currency->countries[0])) {
                $currencyCultureCodes[$currency->code] = \Utils::getCultureCode(null, $currency->countries[0]);
            } else {
                $currencyCultureCodes[$currency->code] = 'en-US';
            }
        }

        Cache::put('CurrencyCultureCode', $currencyCultureCodes, 3600);

        return $currencyCultureCodes;
    }

    /**
     * Format currency
     * @return type
     */
    public static function formatCurrency($numberFormatter, $price, $currency)
    {
        if (in_array($currency->code, static::$zeroAtTheEnd)) {
            $fractionDigits = 0;
            $numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $fractionDigits);
        }

        $priceText = $numberFormatter->formatCurrency($price, $currency->code);

        // check use symbol from db for formatting
        if (isset(static::$useDBSymbol[$currency->code])) {             
            $priceText = str_replace(static::$useDBSymbol[$currency->code], $currency->symbol, $priceText);
        }

        return $priceText;
    }

}
