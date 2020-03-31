<?php

namespace App\Mappers;

class StripeCodeMapper
{
    private const COMMON_PHRASE = 'card.error.common';

    /**
     * Error code -> phrase map
     * @var array $phrase_map
     */
    private static array $phrase_map = [
        'incorrect_number' => 'card.error.number_incorrect',
        'incorrect_cvc' => 'card.error.cvv_incorrect',
        'incorrect_address' => 'card.error.address_incorrect',
        'incorrect_zip' => 'card.error.postcode_lost',
        'invalid_cvc' => 'card.error.cvv_incorrect',
        'invalid_number' => 'card.error.number_incorrect',
        'invalid_expiry_month' => 'card.error.due_date_incorrect',
        'invalid_expiry_year' => 'card.error.due_date_incorrect',
        'balance_insufficient' => 'card.error.funds_insufficient',
        'insufficient_funds' => 'card.error.funds_insufficient',
        'postal_code_invalid' => 'card.error.postcode_lost',
        'expired_card' => 'card.error.expired',
        'email_invalid' => 'card.error.email_incorrect',
        'card_declined' => 'card.error.not_functioning'
    ];

    /**
     * List of fallback codes
     * @var array $fallback_codes
     */
    private static array $fallback_codes = [
        'api_key_expired', 'bank_account_declined', 'card_decline_rate_limit_exceeded', 'country_unsupported', 'customer_max_payment_methods',
        'do_not_honor', 'do_not_try_again', 'currency_not_supported', 'generic_decline', 'issuer_not_available', 'merchant_blacklist',
        'processing_error', 'reenter_transaction', 'restricted_card', 'revocation_of_all_authorizations', 'revocation_of_authorization',
        'try_again_later', 'authentication_required'
    ];

    /**
     * Map code to phrase
     * @param string|null $decline_code
     * @param string|null $general_code
     * @return string
     */
    public static function toPhrase(?string $decline_code = null, ?string $general_code = null): string
    {
        if (!empty($decline_code) && isset(static::$phrase_map[$decline_code])) {
            return static::$phrase_map[$decline_code];
        } elseif (!empty($decline_code) && isset(static::$phrase_map[$general_code])) {
            return static::$phrase_map[$general_code];
        }
        return self::COMMON_PHRASE;
    }

    /**
     * Checks for fallback
     * @param string|null $decline_code
     * @param string|null $general_code
     * @return bool
     */
    public static function isFallback(?string $decline_code = null, ?string $general_code = null): bool
    {
        $result = in_array($decline_code, self::$fallback_codes);

        return in_array($general_code, self::$fallback_codes);
    }
}
