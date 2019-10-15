<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Jenssegers\Mongodb\Eloquent\Model;

class I18n extends Model
{
    protected $collection = 'i18n';

    protected $dates = ['created_at', 'updated_at'];

    public $timestamps = true;

    public static $loadedPhrases = [];

    protected static $languages = [
        'en'     => 'English',
        'fi'     => 'Finnish',
        'pl'     => 'Polish',
        'my'     => 'Malay',
        'hu'     => 'Hungarian',
        'tr'     => 'Turkish',
        'id'     => 'Indonesian',
        'ar'     => 'Arabic',
        'ko'     => 'Korean',
        'ja'     => 'Japanese',
        'no'     => 'Norwegian',
        'sv'     => 'Swedish',
        'es'     => 'Spanish',
        'fr'     => 'French',
        'it'     => 'Italian',
        'nl'     => 'Dutch',
        'de'     => 'German',
        'ru'     => 'Russian',
        'pt'     => 'Portuguese',
        'da'     => 'Danish',
        'cs'     => 'Czech',
        'he'     => 'Hebrew',
        'el'     => 'Greek',
        'br'     => 'Brazilian portuguese',
        'th'     => 'Thai',
        'af'     => 'Afrikaans',
        'hi'     => 'Hindi',
        'bg'     => 'Bulgarian',
        'hr'     => 'Croatian',
        'ro'     => 'Romanian',
        'zh'     => 'Chinese',
        'ee'     => 'Estonian',
        'lv'     => 'Latvian',
        'lt'     => 'Lithuanian',
        'sk'     => 'Slovak',
        'mt'     => 'Maltese',
        'sl'     => 'Slovenian',
        'ur'     => 'Urdu',
        'jv'     => 'Javanese',
        'bn'     => 'Bengali',
        'vi'     => 'Vietnamese',
        'sr'     => 'Serbian',
        'is'     => 'Icelandic',
        'uk'     => 'Ukrainian',
        'tw'     => 'Traditional Chinese Taiwan',
    ];

    public static $browser_codes = [
        'ms'  => 'my',
        'pt_BR' => 'br',
        'zh_TW' => 'tw',
    ];

    /**
     * Saga I18n::$placeholders
     * @var type
     */
    public static $placeholders = [
        '#FIRST_NAME#',
        '#LAST_NAME#',
        '#CUSTOMER#',
        '#PRODUCT#',
        '#FIRST_NAME#',
        '#COMPANY#',
        '#AFFILIATE_ID#',
        '#TRACKING_NUMBER#',
        '#COUNTRY#',
        '#ORDER#',
        '#TRACKING_LINK#',
        '#CODE#',
        '#DOMAIN#',
        '#LANGUAGE#',
        '#SHIPPING_DAYS#',
        '#SUPPORTER#',
        '#TICKETDATE#',
        '#IBAN#',
        '#PAYMENT_HASH#',
        '#BIC#',
        '#PAYMENT_DETAILS#',
        '#STATUS#',
        '#STATUS_CLARIFICATION#',
        '#PRODUCTNAME#',
        '#SHOPNAME#',
        '#SUPPORT_TEAM#',
        '#AMOUNT#',
        '#COUNT#',
        '#ADDRESS#',
        '#SAGA_LINK#',
        '#CANCEL_LINK#',
        '#CUSTOMER_NUMBER#',
        '#PRODUCTS#',
        '#SURVEY_LINK',
        '#DATE#,'
    ];

    /**
     * Returns translation languages array
     *
     * @param bool $codes_only
     * @return array
     */
    public static function getTranslationLanguages(bool $codes_only = false): array
    {
        $langs = static::$languages;        
        $langs = array_merge($langs, I18n::$browser_codes);
        if ($codes_only) {
            $langs = array_keys($langs);
        }

        return $langs;
    }
}
