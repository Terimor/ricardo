<?php

namespace App\Mappers;

class BluesnapCodeMapper
{
    private const CODE_COMMON = '*';

    /**
     * @var array $map
     */
    private static $map = [
        self::CODE_COMMON       => 'card.error.common',
        'CVV_ERROR'             => 'card.error.cvv_incorrect',
        'INSUFFICIENT_FUNDS'    => 'card.error.funds_insufficient',
        'NVALID_CARD_NUMBER'    => 'card.error.number_incorrect',
        'EXPIRED_CARD'          => 'card.error.expired',
        'LIMIT_EXCEEDED'        => 'card.error.month_limit'
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
