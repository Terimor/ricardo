<?php

namespace App\Mappers;

class StripeCodeMapper
{
    private const COMMON_PHRASE = 'card.error.common';

    /**
     * @var array $map
     */
    private static $map = [
        'incorrect_number'  => 'card.error.number_incorrect',
        'incorrect_cvc'     => 'card.error.cvv_incorrect',
        'incorrect_address' => 'card.error.address_incorrect',
        'incorrect_zip'     => 'card.error.postcode_lost',
        'invalid_cvc'       => 'card.error.cvv_incorrect',
        'invalid_number'    => 'card.error.number_incorrect',
        'invalid_expiry_month'  => 'card.error.due_date_incorrect',
        'invalid_expiry_year'   => 'card.error.due_date_incorrect',
        'balance_insufficient'  => 'card.error.funds_insufficient',
        'postal_code_invalid'   => 'card.error.postcode_lost',
        'expired_card'      => 'card.error.expired',
        'email_invalid'     => 'card.error.email_incorrect',
        'card_declined'     => 'card.error.not_functioning'
    ];

    /**
     * Map code to phrase
     * @param  string|null $code
     * @return string
     */
    public static function toPhrase(?string $code = null): string
    {
        if (!empty($code) && isset(static::$map[$code])) {
            return static::$map[$code];
        }
        return self::COMMON_PHRASE;
    }
}
