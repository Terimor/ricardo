<?php

namespace App\Mappers;

class NovalnetCodeMapper
{
    private const COMMON_PHRASE = 'card.error.common';

    /**
     * @var array $code_map
     */
    private static array $code_map = [
        '406007' => 'card.error.expired',
        '406002' => 'card.error.cvv_incorrect'
    ];

    /**
     * Returns a phrase by code if one exists
     * @param  string|null $code
     * @return string|null
     */
    public static function getPhrase(?string $code): ?string
    {
        if ($code && isset(static::$code_map[$code])) {
            return static::$code_map[$code];
        }
        return null;
    }

    /**
     * Map code to phrase
     * @param  string|null $code
     * @return string
     */
    public static function toPhrase(?string $code = null): string
    {
        return static::getPhrase($code) ?? self::COMMON_PHRASE;
    }
}
