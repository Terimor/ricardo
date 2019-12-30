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
        'DO_NOT_HONOR'          => 'card.error.not_functioning',
        'FRAUD_DETECTED'        => 'card.error.not_functioning',
        'CVV_ERROR'             => 'card.error.cvv_incorrect',
        'INSUFFICIENT_FUNDS'    => 'card.error.funds_insufficient',
        'NVALID_CARD_NUMBER'    => 'card.error.number_incorrect',
        'EXPIRED_CARD'          => 'card.error.expired',
        'LIMIT_EXCEEDED'        => 'card.error.month_limit',
        'securityCode'          => 'card.error.cvv_incorrect'
    ];


    /**
     * Returns a phrase by code if one exists
     * @param  string|null $code
     * @return string|null
     */
    public static function getPhrase(?string $code = null): ?string
    {
        if ($code && isset(static::$map[$code])) {
            return static::$map[$code];
        }
        return null;
    }

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
