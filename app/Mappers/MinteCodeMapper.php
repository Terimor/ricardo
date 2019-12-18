<?php

namespace App\Mappers;

class MinteCodeMapper
{
    private const COMMON_PHRASE = 'card.error.common';

    /**
     * @var array $code_map
     */
    private static $code_map = [
        '05'    => 'card.error.not_functioning',
        '51'    => 'card.error.funds_insufficient',
        '54'    => 'card.error.expired',
        '57'    => 'card.error.not_functioning'
    ];

    /**
     * @var array $message_map
     */
    private static $message_map = [
        'Invalid cardnumber value'  => 'card.error.number_incorrect',
        'cvv has invalid format'    => 'card.error.cvv_incorrect'
    ];

    /**
     * Map code to phrase
     * @param  string|null $code
     * @param  string|null $msg
     * @return string
     */
    public static function toPhrase(?string $code = null, ?string $msg = null): string
    {
        if (!empty($code) && isset(static::$code_map[$code])) {
            return static::$code_map[$code];
        }
        $msg = preg_replace("/[^a-zA-Z\s]+/", '', $msg);
        if (!empty($msg) && isset(static::$message_map[$msg])) {
            return static::$message_map[$msg];
        }
        return self::COMMON_PHRASE;
    }
}
