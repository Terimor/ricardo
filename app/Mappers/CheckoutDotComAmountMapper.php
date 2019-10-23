<?php

namespace App\Mappers;

class CheckoutDotComAmountMapper
{
    const CURRENCY_REST = '*';

    /**
     * @var array $map
     */
    private static $map = [
        'BIF' => ['mltpl' => 1, 'divider' => 1, 'modulo' => 0],
        'DJF' => ['mltpl' => 1, 'divider' => 1, 'modulo' => 0],
        'GNF' => ['mltpl' => 1, 'divider' => 1, 'modulo' => 0],
        'ISK' => ['mltpl' => 1, 'divider' => 1, 'modulo' => 0],
        'KMF' => ['mltpl' => 1, 'divider' => 1, 'modulo' => 0],
        'XAF' => ['mltpl' => 1, 'divider' => 1, 'modulo' => 0],
        'CLF' => ['mltpl' => 1, 'divider' => 1, 'modulo' => 0],
        'XPF' => ['mltpl' => 1, 'divider' => 1, 'modulo' => 0],
        'JPY' => ['mltpl' => 1, 'divider' => 1, 'modulo' => 0],
        'PYG' => ['mltpl' => 1, 'divider' => 1, 'modulo' => 0],
        'RWF' => ['mltpl' => 1, 'divider' => 1, 'modulo' => 0],
        'KRW' => ['mltpl' => 1, 'divider' => 1, 'modulo' => 0],
        'VUV' => ['mltpl' => 1, 'divider' => 1, 'modulo' => 0],
        'VND' => ['mltpl' => 1, 'divider' => 1, 'modulo' => 0],
        'XOF' => ['mltpl' => 1, 'divider' => 1, 'modulo' => 0],
        'BHD' => ['mltpl' => 1000, 'divider' => 10, 'modulo' => 0],
        'LYD' => ['mltpl' => 1000, 'divider' => 10, 'modulo' => 0],
        'JOD' => ['mltpl' => 1000, 'divider' => 10, 'modulo' => 0],
        'KWD' => ['mltpl' => 1000, 'divider' => 10, 'modulo' => 0],
        'OMR' => ['mltpl' => 1000, 'divider' => 10, 'modulo' => 0],
        'TND' => ['mltpl' => 1000, 'divider' => 10, 'modulo' => 0],
        'CLP' => ['mltpl' => 100, 'divider' => 100, 'modulo' => 0],
        self::CURRENCY_REST => ['mltpl' => 100, 'divider' => 1, 'modulo' => 0],
    ];

    /**
     * Normalize amount for provider
     * @param  float    $amount
     * @param  string   $currency
     * @return int
     */
    public static function toProvider(float $amount, string $currency): float
    {
        $currency = strtoupper($currency);
        if (isset(static::$map[$currency])) {
            return $amount * static::$map[$currency]['mltpl'];
        }
        return $amount * static::$map[self::CURRENCY_REST]['mltpl'];
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
        return $amount / static::$map[self::CURRENCY_REST]['mltpl'];
    }
}
