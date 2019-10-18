<?php

namespace App\Mappers;

class CheckoutDotComCodeMapper
{
    private const CODE_COMMON = '*';

    /**
     * @var array $map
     */
    private static $map = [
        '20014' => 'card.error.number_incorrect',
        '20046' => 'card.error.not_functioning',
        '20051' => 'card.error.funds_insufficient',
        '20054' => 'card.error.expired',
        '20055' => 'card.error.cvv_incorrect',
        '20056' => 'card.error.not_functioning',
        '20061' => 'card.error.funds_insufficient',
        '20087' => 'card.error.cvv_incorrect',
        '20093' => 'card.error.not_functioning',
        '20103' => 'card.error.not_functioning',
        '20107' => 'card.error.address_incorrect',
        '20150' => 'card.error.not_functioning',
        '20151' => 'card.error.not_functioning',
        '20152' => 'card.error.not_functioning',
        '20154' => 'card.error.not_functioning',
        '200P1' => 'card.error.funds_insufficient',
        '200P9' => 'card.error.funds_insufficient',
        '200T3' => 'card.error.not_functioning',
        '30033' => 'card.error.expired',
        'card_number_invalid'   => 'card.error.number_incorrect',
        self::CODE_COMMON       => 'card.error.common'
    ];

    /**
     * Map code to phrase
     * @param  string $code
     * @return string
     */
    public static function toPhrase(string $code = self::CODE_COMMON): string
    {
        if (isset(static::$map[$code])) {
            return static::$map[$code];
        }
        return static::$map[self::CODE_COMMON];
    }
}
