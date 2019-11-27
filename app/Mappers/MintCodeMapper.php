<?php

namespace App\Mappers;

class MintCodeMapper
{
    private const CODE_COMMON = '*';

    /**
     * @var array $map
     */
    private static $map = [
        self::CODE_COMMON => 'card.error.common',
        'I-204' => 'card.error.common'
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
