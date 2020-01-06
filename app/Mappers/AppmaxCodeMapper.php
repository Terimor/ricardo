<?php

namespace App\Mappers;

class AppmaxCodeMapper
{
    private const CODE_COMMON = '*';

    /**
     * @var array $map
     */
    private static $map = [
        self::CODE_COMMON   => 'card.error.common'
    ];

    /**
     * @var array $message_map
     */
    private static $message_map = [
        'Transação não autorizada, motivo: Cartão inválido'  => 'card.error.number_incorrect'
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
