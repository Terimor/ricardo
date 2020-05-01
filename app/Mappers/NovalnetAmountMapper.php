<?php

namespace App\Mappers;

class NovalnetAmountMapper
{
    const CURRENCY_REST = '*';

    /**
     * @var array $map
     */
    private static $map = [
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
