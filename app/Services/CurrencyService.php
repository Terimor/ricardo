<?php

namespace App\Services;
use App\Models\Currency;
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

    public function __construct()
    {
    }

    /**
     * Get local price from USD
     * @param float $price
     * @param string $currency
     * @param string $countryCode
     * @param type $ip
     * @return float
     */
    public static function getLocalPriceFromUsd(float $price, Currency $currency = null, string $countryCode = null, $ip = null) : array
    {
        if (!$currency) {
            $currency = self::getCurrency(null, $countryCode);
        }
        $currencyCode = $currency->code;
        $countryCode = $currency->countryCode;

        //get fraction digits and locale string
        $localeString = \Utils::getCultureCode($ip, $countryCode);
        $numberFormatter = new \NumberFormatter($localeString, \NumberFormatter::CURRENCY);
        $fractionDigits = $numberFormatter->getAttribute(\NumberFormatter::MAX_FRACTION_DIGITS);
        // if price < 4.5 set minimum price = 4.5
        if ($price < 4.5) {
            logger()->error("Price < 4.5", ['price' => $price]);
            $price = 4.5;
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
     * Get currency array
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
            if (request()->input('cur')) {
                $currencyCode = request()->input('cur');
            }
        }

        if (!$currencyCode) {
            if (!$countryCode) {
                $countryCode = strtoupper(\Utils::getLocationCountryCode());
            }

            $localeString = \Utils::getCultureCode(null, $countryCode);
            $numberFormatter = new \NumberFormatter($localeString, \NumberFormatter::CURRENCY);
            $currencyCode = $numberFormatter->getTextAttribute(\NumberFormatter::CURRENCY_CODE);
        }

        if (!$currency) {
            $currency = Currency::whereCode($currencyCode)->where('status', 'active')->first();
        }

        if (!empty($currency->countries)){
            if (!in_array(strtolower($countryCode), $currency->countries)) {
                $countryCode = $currency->countries[0];
            }
        } else {
            logger()->error("Can't find currency country", ['currency' => $currency ? $currency->toArray() : '', 'currencyCode' => $currencyCode]);
            //try to find in currency countries
            if ($countryCode) {
                $currency = Currency::where(['countries' => strtolower($countryCode)])->where('status', 'active')->first();

                if(!$currency) {
                    $currencyCode = 'USD';
                }
            } else {
                $currencyCode = 'USD';
            }
            $currency = Currency::whereCode($currencyCode)->first();
            $countryCode = !empty($currency->countries[0]) ? $currency->countries[0] : 'US';
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
	public static function getLocalTextValue(float $price, Currency $currency = null, string $countryCode = null) : string
	{
		if (!$countryCode) {
			if (!$currency) {
				$currency = self::getCurrency(null, $countryCode);
			}
			$currencyCode = $currency->code;
			$countryCode = !empty($currency->countryCode) ? $currency->countryCode : (!empty($currency->countries[0]) ? $currency->countries[0] : 'us');
		}

        //get fraction digits and locale string
        $localeString = \Utils::getCultureCode(null, $countryCode);
        $numberFormatter = new \NumberFormatter($localeString, \NumberFormatter::CURRENCY);

		return $numberFormatter->formatCurrency($price, $currencyCode);
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
        $localeString = Cache::get('culture_code_'.$currencyCode);
        
        if (!$localeString) {
            $currency = self::getCurrency($currencyCode);
            $localeString = \Utils::getCultureCode(null, $currency->countryCode);
            Cache::put('culture_code_'.$currencyCode, $localeString, 3600);
        }
        
        return $localeString;
    }

}