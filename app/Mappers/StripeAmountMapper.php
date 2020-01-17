<?php

namespace App\Mappers;

class StripeAmountMapper
{
    /**
     * @var array $map
     */
    private static $map = [
        'USD' => ['mltpl' => 100],
        'AED' => ['mltpl' => 100],
        'AFN' => ['mltpl' => 100],
        'ALL' => ['mltpl' => 100],
        'AMD' => ['mltpl' => 100],
        'ANG' => ['mltpl' => 100],
        'AOA' => ['mltpl' => 100],
        'ARS' => ['mltpl' => 100],
        'AUD' => ['mltpl' => 100],
        'AWG' => ['mltpl' => 100],
        'AZN' => ['mltpl' => 100],
        'BAM' => ['mltpl' => 100],
        'BBD' => ['mltpl' => 100],
        'BDT' => ['mltpl' => 100],
        'BGN' => ['mltpl' => 100],
        'BIF' => ['mltpl' => 1],
        'BMD' => ['mltpl' => 100],
        'BND' => ['mltpl' => 100],
        'BOB' => ['mltpl' => 100],
        'BRL' => ['mltpl' => 100],
        'BSD' => ['mltpl' => 100],
        'BWP' => ['mltpl' => 100],
        'BZD' => ['mltpl' => 100],
        'CAD' => ['mltpl' => 100],
        'CDF' => ['mltpl' => 100],
        'CHF' => ['mltpl' => 100],
        'CLP' => ['mltpl' => 1],
        'CNY' => ['mltpl' => 100],
        'COP' => ['mltpl' => 100],
        'CRC' => ['mltpl' => 100],
        'CVE' => ['mltpl' => 100],
        'CZK' => ['mltpl' => 100],
        'DJF' => ['mltpl' => 1],
        'DKK' => ['mltpl' => 100],
        'DOP' => ['mltpl' => 100],
        'DZD' => ['mltpl' => 100],
        'EGP' => ['mltpl' => 100],
        'ETB' => ['mltpl' => 100],
        'EUR' => ['mltpl' => 100],
        'FJD' => ['mltpl' => 100],
        'FKP' => ['mltpl' => 100],
        'GBP' => ['mltpl' => 100],
        'GEL' => ['mltpl' => 100],
        'GIP' => ['mltpl' => 100],
        'GMD' => ['mltpl' => 100],
        'GNF' => ['mltpl' => 1],
        'GTQ' => ['mltpl' => 100],
        'GYD' => ['mltpl' => 100],
        'HKD' => ['mltpl' => 100],
        'HNL' => ['mltpl' => 100],
        'HRK' => ['mltpl' => 100],
        'HTG' => ['mltpl' => 100],
        'HUF' => ['mltpl' => 100],
        'IDR' => ['mltpl' => 100],
        'ILS' => ['mltpl' => 100],
        'INR' => ['mltpl' => 100],
        'ISK' => ['mltpl' => 100],
        'JMD' => ['mltpl' => 100],
        'JPY' => ['mltpl' => 1],
        'KES' => ['mltpl' => 100],
        'KGS' => ['mltpl' => 100],
        'KHR' => ['mltpl' => 100],
        'KMF' => ['mltpl' => 1],
        'KRW' => ['mltpl' => 1],
        'KYD' => ['mltpl' => 100],
        'KZT' => ['mltpl' => 100],
        'LAK' => ['mltpl' => 100],
        'LBP' => ['mltpl' => 100],
        'LKR' => ['mltpl' => 100],
        'LRD' => ['mltpl' => 100],
        'LSL' => ['mltpl' => 100],
        'MAD' => ['mltpl' => 100],
        'MDL' => ['mltpl' => 100],
        'MGA' => ['mltpl' => 1],
        'MKD' => ['mltpl' => 100],
        'MMK' => ['mltpl' => 100],
        'MNT' => ['mltpl' => 100],
        'MOP' => ['mltpl' => 100],
        'MRO' => ['mltpl' => 100],
        'MUR' => ['mltpl' => 100],
        'MVR' => ['mltpl' => 100],
        'MWK' => ['mltpl' => 100],
        'MXN' => ['mltpl' => 100],
        'MYR' => ['mltpl' => 100],
        'MZN' => ['mltpl' => 100],
        'NAD' => ['mltpl' => 100],
        'NGN' => ['mltpl' => 100],
        'NIO' => ['mltpl' => 100],
        'NOK' => ['mltpl' => 100],
        'NPR' => ['mltpl' => 100],
        'NZD' => ['mltpl' => 100],
        'PAB' => ['mltpl' => 100],
        'PEN' => ['mltpl' => 100],
        'PGK' => ['mltpl' => 100],
        'PHP' => ['mltpl' => 100],
        'PKR' => ['mltpl' => 100],
        'PLN' => ['mltpl' => 100],
        'PYG' => ['mltpl' => 1],
        'QAR' => ['mltpl' => 100],
        'RON' => ['mltpl' => 100],
        'RSD' => ['mltpl' => 100],
        'RUB' => ['mltpl' => 100],
        'RWF' => ['mltpl' => 1],
        'SAR' => ['mltpl' => 100],
        'SBD' => ['mltpl' => 100],
        'SCR' => ['mltpl' => 100],
        'SEK' => ['mltpl' => 100],
        'SGD' => ['mltpl' => 100],
        'SHP' => ['mltpl' => 100],
        'SLL' => ['mltpl' => 100],
        'SOS' => ['mltpl' => 100],
        'SRD' => ['mltpl' => 100],
        'STD' => ['mltpl' => 100],
        'SZL' => ['mltpl' => 100],
        'THB' => ['mltpl' => 100],
        'TJS' => ['mltpl' => 100],
        'TOP' => ['mltpl' => 100],
        'TRY' => ['mltpl' => 100],
        'TTD' => ['mltpl' => 100],
        'TWD' => ['mltpl' => 100],
        'TZS' => ['mltpl' => 100],
        'UAH' => ['mltpl' => 100],
        'UGX' => ['mltpl' => 1],
        'UYU' => ['mltpl' => 100],
        'UZS' => ['mltpl' => 100],
        'VND' => ['mltpl' => 1],
        'VUV' => ['mltpl' => 1],
        'WST' => ['mltpl' => 100],
        'XAF' => ['mltpl' => 1],
        'XCD' => ['mltpl' => 100],
        'XOF' => ['mltpl' => 1],
        'XPF' => ['mltpl' => 1],
        'YER' => ['mltpl' => 100],
        'ZAR' => ['mltpl' => 100],
        'ZMW' => ['mltpl' => 100]
    ];

    /**
     * Normalize amount for provider
     * @param  float    $amount
     * @param  string   $currency
     * @return int|null
     */
    public static function toProvider(float $amount, string $currency): ?int
    {
        $currency = strtoupper($currency);
        if (isset(static::$map[$currency])) {
            return $amount * static::$map[$currency]['mltpl'];
        }
        return null;
    }

    /**
     * Normalize amount from provider
     * @param  int      $amount
     * @param  string   $currency
     * @return float
     */
    public static function fromProvider(int $amount, string $currency): float
    {
        $currency = strtoupper($currency);
        if (isset(static::$map[$currency])) {
            return $amount / static::$map[$currency]['mltpl'];
        }
        return null;
    }
}
