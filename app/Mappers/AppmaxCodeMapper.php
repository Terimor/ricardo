<?php

namespace App\Mappers;

class AppmaxCodeMapper
{
    private const COMMON_PHRASE = 'card.error.common';

    /**
     * @var array $map
     */
    private static $map = [
        'Transação não autorizada, motivo: Cartão inválido'  => 'card.error.number_incorrect'
    ];

    /**
     * Map code to phrase
     * @param  string|null $msg
     * @return string
     */
    public static function toPhrase(?string $msg = null): string
    {
        if (!empty($msg) && isset(static::$map[$msg])) {
            return static::$map[$msg];
        }
        return self::COMMON_PHRASE;
    }
}
