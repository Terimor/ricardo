<?php

namespace App\Mappers;

class EbanxCodeMapper
{
    private const CODE_COMMON = '*';

    /**
     * @var array $map
     */
    private static $map = [
        self::CODE_COMMON => 'card.error.common',
        'BP-DR-55'  => 'card.error.cvv_incorrect',
        'BP-DR-112' => 'card.error.funds_insufficient',
        'BP-DR-75'  => 'card.error.number_incorrect',
        'BP-DR-101' => 'card.error.not_functioning',
        'BP-DR-17'  => 'card.error.address_incorrect',
        'BP-DR-94'  => 'card.error.expired',
        'BP-DR-21'  => 'card.error.age_restriction',
        'BP-DR-23'  => 'card.error.doc_number_incorrect',
        'BP-DR-39'  => 'card.error.doc_name_incorrect',
        'BP-DR-49'  => 'card.error.number_lost',
        'BP-DR-51'  => 'card.error.cardholder_lost',
        'BP-DR-56'  => 'card.error.due_date_lost',
        'BP-DR-67'  => 'card.error.due_date_incorrect',
        'BP-DR-83'  => 'card.error.cannot_be_processed',
        'BP-DR-117' => 'card.error.installments_lower_limit',
        'BP-DR-13'  => 'card.error.name_lost',
        'BP-DR-15'  => 'card.error.email_lost',
        'BP-DR-17'  => 'card.error.email_incorrect',
        'BP-DR-19'  => 'card.error.birthdate_lost',
        'BP-DR-20'  => 'card.error.birthdate_incorrect',
        'BP-DR-24'  => 'card.error.postcode_lost',
        'BP-DR-25'  => 'card.error.address_lost',
        'BP-DR-26'  => 'card.error.street_number_lost',
        'BP-DR-27'  => 'card.error.city_lost',
        'BP-DR-28'  => 'card.error.state_lost',
        'BP-DR-29'  => 'card.error.state_incorrect',
        'BP-DR-32'  => 'card.error.phone_incorrect',
        'BP-DR-39'  => 'card.error.cpf_incorrect',
        'BP-DR-40'  => 'card.error.month_limit',
        'BP-DR-90'  => 'card.error.blacklist'
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
