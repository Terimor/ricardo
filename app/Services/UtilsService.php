<?php

namespace App\Services;

use App\Models\{Currency, OdinProduct, Setting, Pixel, AwsImage, Domain, OdinOrder};
use MongoDB\BSON\{ObjectId, UTCDateTime};
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use DeviceDetector\DeviceDetector;

/**
 * Utils Service class
 */
class UtilsService
{
    /**
     * Default Amazon s3 URL
     */
	const S3_URL_STAGING = 'odin-img-dev.s3.eu-central-1.amazonaws.com';
    const S3_URL_PRODUCTION = 'mediaodin.s3.amazonaws.com';
    const CDN_HOST_PRODUCTION = 'cdn.8xgb.com';
    const CDN_HOST_STAGING = 'cdn.odin.saga-be.host';

	public static $localhostIps = ['127.0.0.1', '192.168.1.101', '192.168.1.3'];
    /**
     * Array using global parameters on site
     */
    public static $globalGetParameters = ['product', 'cur', 'tpl', '_ip'];

    /**
     * Culture codes (for numberFormatter)
     * Two Letter Country Code -> Culture Info Code
     * Linked to Saga: GeoConstants::$cultures
     * https://datahub.io/core/language-codes
     * https://support.conga.com/Reference/Supported_Culture_Codes
     */
    public static $cultureCodes = [
        'ad' => 'ca-AD',
        'ae' => 'en-AE',
        'af' => 'fa-AF',
        'ag' => 'en-AG',
        'ai' => 'en-AI',
        'aq' => 'en-US',
        'al' => 'sq-AL',
        'am' => 'hy-AM',
        'an' => 'nl-NL',
        'ao' => 'ln-AO',
        'ar' => 'es-AR',
        'as' => 'en-AS',
        'at' => 'de-AT',
        'au' => 'en-AU',
        'aw' => 'nl-AW',
        'ax' => 'sv-AX',
        'az' => 'az-AZ',
        'ba' => 'hr-BA',
        'bb' => 'en-BB',
        'bd' => 'bn-BD',
        'be' => 'nl-BE',
        'bf' => 'fr-BF',
        'bg' => 'bg-BG',
        'bh' => 'ar-BH',
        'bi' => 'fr-BI',
        'bj' => 'fr-BJ',
        'bl' => 'fr-BL',
        'bm' => 'en-BM',
        'bn' => 'ms-BN',
        'bo' => 'es-BO',
        'bq' => 'nl-BQ',
        'br' => 'pt-BR',
        'bs' => 'en-BS',
        'bt' => 'dz-BT',
        'bv' => 'en-US',
        'bw' => 'en-BW',
        'by' => 'be-BY',
        'bz' => 'en-BZ',
        'ca' => 'en-CA',
        'cc' => 'en-CC',
        'cd' => 'fr-CD',
        'cf' => 'fr-CF',
        'cg' => 'fr-CG',
        'ch' => 'de-CH',
        'ci' => 'fr-CI',
        'ck' => 'en-CK',
        'cl' => 'es-CL',
        'cm' => 'fr-CM',
        'cn' => 'zh-CN',
        'co' => 'es-CO',
        'cr' => 'es-CR',
        'cu' => 'es-CU',
        'cv' => 'pt-CV',
        'cw' => 'nl-CW',
        'cx' => 'en-CX',
        'cy' => 'el-CY',
        'cz' => 'cs-CZ',
        'de' => 'de-DE',
        'dg' => 'en-DG',
        'dj' => 'fr-DJ',
        'dk' => 'da-DK',
        'dm' => 'en-DM',
        'do' => 'es-DO',
        'dz' => 'fr-DZ',
        'ea' => 'es-EA',
        'ec' => 'es-EC',
        'ee' => 'et-EE',
        'eg' => 'ar-EG',
        'eh' => 'ar-EH',
        'er' => 'ar-ER',
        'es' => 'es-ES',
        'et' => 'am-ET',
        'fi' => 'fi-FI',
        'fj' => 'en-FJ',
        'fk' => 'en-FK',
        'fm' => 'en-FM',
        'fo' => 'fo-FO',
        'fr' => 'fr-FR',
        'ga' => 'fr-GA',
        'gb' => 'en-GB',
        'gd' => 'en-GD',
        'ge' => 'ka-GE',
        'gf' => 'fr-GF',
        'gg' => 'en-GG',
        'gh' => 'ak-GH',
        'gi' => 'en-GI',
        'gl' => 'da-GL',
        'gm' => 'en-GM',
        'gn' => 'fr-GN',
        'gp' => 'fr-GP',
        'gq' => 'es-GQ',
        'gr' => 'el-GR',
        'gt' => 'es-GT',
        'gu' => 'en-GU',
        'gw' => 'pt-GW',
        'gy' => 'en-GY',
        'hk' => 'en-HK',
        'hn' => 'es-HN',
        'hr' => 'hr-HR',
        'ht' => 'fr-HT',
        'hu' => 'hu-HU',
        'ic' => 'es-IC',
        'id' => 'id-ID',
        'ie' => 'en-IE',
        'il' => 'he-IL',
        'im' => 'gv-IM',
        'in' => 'hi-IN',
        'io' => 'en-IO',
        'iq' => 'ar-IQ',
        'ir' => 'fa-IR',
        'is' => 'is-IS',
        'it' => 'it-IT',
        'je' => 'en-JE',
        'jm' => 'en-JM',
        'jo' => 'ar-JO',
        'jp' => 'ja-JP',
        'ke' => 'sw-KE',
        'kg' => 'ky-KG',
        'kh' => 'km-KH',
        'ki' => 'en-KI',
        'km' => 'fr-KM',
        'kn' => 'en-KN',
        'kp' => 'ko-KP',
        'kr' => 'ko-KR',
        'kw' => 'ar-KW',
        'ky' => 'en-KY',
        'kz' => 'kk-KZ',
        'la' => 'lo-LA',
        'lb' => 'ar-LB',
        'lc' => 'en-LC',
        'li' => 'de-LI',
        'lk' => 'si-LK',
        'lr' => 'en-LR',
        'ls' => 'en-LS',
        'lt' => 'lt-LT',
        'lu' => 'fr-LU',
        'lv' => 'lv-LV',
        'ly' => 'ar-LY',
        'ma' => 'ar-MA',
        'mc' => 'fr-MC',
        'md' => 'ro-MD',
        'me' => 'sr-ME',
        'mf' => 'fr-MF',
        'mg' => 'mg-MG',
        'mh' => 'en-MH',
        'mk' => 'mk-MK',
        'ml' => 'bm-ML',
        'mm' => 'my-MM',
        'mn' => 'mn-MN',
        'mo' => 'zh-MO',
        'mp' => 'en-MP',
        'mq' => 'fr-MQ',
        'mr' => 'fr-MR',
        'ms' => 'en-MS',
        'mt' => 'mt-MT',
        'mv' => 'dv-MV',
        'mu' => 'fr-MU',
        'mw' => 'en-MW',
        'mx' => 'es-MX',
        'my' => 'ms-MY',
        'mz' => 'pt-MZ',
        'na' => 'af-NA',
        'nc' => 'fr-NC',
        'ne' => 'fr-NE',
        'nf' => 'en-NF',
        'ng' => 'ig-NG',
        'ni' => 'es-NI',
        'nl' => 'nl-NL',
        'no' => 'nb-NO',
        'np' => 'ne-NP',
        'nr' => 'en-NR',
        'nu' => 'en-NU',
        'nz' => 'en-NZ',
        'om' => 'ar-OM',
        'pa' => 'es-PA',
        'pe' => 'es-PE',
        'pf' => 'fr-PF',
        'pg' => 'en-PG',
        'ph' => 'en-PH',
        'pk' => 'ur-PK',
        'pl' => 'pl-PL',
        'pm' => 'fr-PM',
        'pn' => 'en-PN',
        'pr' => 'es-PR',
        'ps' => 'ar-PS',
        'pt' => 'pt-PT',
        'pw' => 'en-PW',
        'py' => 'es-PY',
        'qa' => 'ar-QA',
        're' => 'fr-RE',
        'ro' => 'ro-RO',
        'rs' => 'sr-RS',
        'ru' => 'ru-RU',
        'rw' => 'rw-RW',
        'sa' => 'ar-SA',
        'sb' => 'en-SB',
        'sc' => 'fr-SC',
        'sd' => 'ar-SD',
        'se' => 'sv-SE',
        'sg' => 'zh-SG',
        'sh' => 'en-SH',
        'si' => 'sl-SI',
        'sj' => 'nb-SJ',
        'sk' => 'sk-SK',
        'sl' => 'en-SL',
        'sm' => 'it-SM',
        'sn' => 'wo-SN',
        'so' => 'so-SO',
        'sr' => 'nl-SR',
        'ss' => 'ar-SS',
        'st' => 'pt-ST',
        'sv' => 'es-SV',
        'sx' => 'nl-SX',
        'sy' => 'ar-SY',
        'sz' => 'en-SZ',
        'tc' => 'en-TC',
        'td' => 'fr-TD',
        'tg' => 'fr-TG',
        'th' => 'th-TH',
        'tj' => 'tg-TJ',
        'tk' => 'en-TK',
        'tl' => 'pt-TL',
        'tm' => 'tk-TM',
        'tn' => 'ar-TN',
        'to' => 'to-TO',
        'tr' => 'tr-TR',
        'tt' => 'en-TT',
        'tv' => 'en-TV',
        'tw' => 'zh-TW',
        'tz' => 'sw-TZ',
        'ua' => 'uk-UA',
        'ug' => 'sw-UG',
        'um' => 'en-UM',
        'us' => 'en-US',
        'uy' => 'es-UY',
        'uz' => 'uz-UZ',
        'va' => 'it-VA',
        'vc' => 'en-VC',
        've' => 'es-VE',
        'vg' => 'en-VG',
        'vi' => 'en-VI',
        'vn' => 'vi-VN',
        'vu' => 'en-VU',
        'wf' => 'fr-WF',
        'ws' => 'en-WS',
        'xk' => 'sq-XK',
        'ye' => 'ar-YE',
        'yt' => 'fr-YT',
        'za' => 'af-ZA',
        'zm' => 'en-ZM',
        'zw' => 'en-ZW'
    ];

    /**
     * Country codes
     * Linked to Saga: GeoConstants::$countries
     */
    public static $countryCodes = [
        'af' => 'Afghanistan',
        'ax' => 'Aland Islands',
        'al' => 'Albania',
        'dz' => 'Algeria',
        'as' => 'American Samoa',
        'ad' => 'Andorra',
        'ao' => 'Angola',
        'ai' => 'Anguilla',
        'aq' => 'Antarctica',
        'ag' => 'Antigua and Barbuda',
        'ar' => 'Argentina',
        'am' => 'Armenia',
        'an' => 'Netherlands Antilles',
        'aw' => 'Aruba',
        'au' => 'Australia',
        'at' => 'Austria',
        'az' => 'Azerbaijan',
        'bs' => 'Bahamas',
        'bh' => 'Bahrain',
        'bd' => 'Bangladesh',
        'bb' => 'Barbados',
        'by' => 'Belarus',
        'be' => 'Belgium',
        'bz' => 'Belize',
        'bj' => 'Benin',
        'bm' => 'Bermuda',
        'bt' => 'Bhutan',
        'bo' => 'Bolivia',
        'bq' => 'Bonaire, Saint Eustatius and Saba',
        'ba' => 'Bosnia and Herzegovina',
        'bw' => 'Botswana',
        'bv' => 'Bouvet Island',
        'br' => 'Brazil',
        'io' => 'British Indian Ocean Territory',
        'vg' => 'British Virgin Islands',
        'bn' => 'Brunei',
        'bg' => 'Bulgaria',
        'bf' => 'Burkina Faso',
        'bi' => 'Burundi',
        'kh' => 'Cambodia',
        'cm' => 'Cameroon',
        'ca' => 'Canada',
        'cv' => 'Cape Verde',
        'ky' => 'Cayman Islands',
        'cf' => 'Central African Republic',
        'td' => 'Chad',
        'cl' => 'Chile',
        'cn' => 'China',
        'cx' => 'Christmas Island',
        'cc' => 'Cocos Islands',
        'co' => 'Colombia',
        'km' => 'Comoros',
        'ck' => 'Cook Islands',
        'cr' => 'Costa Rica',
        'hr' => 'Croatia',
        'cu' => 'Cuba',
        'cw' => 'Curacao',
        'cy' => 'Cyprus',
        'cz' => 'Czech Republic',
        'cd' => 'Democratic Republic of the Congo',
        'dk' => 'Denmark',
        'dj' => 'Djibouti',
        'dm' => 'Dominica',
        'do' => 'Dominican Republic',
        'tl' => 'Timor-Leste',
        'ec' => 'Ecuador',
        'eg' => 'Egypt',
        'sv' => 'El Salvador',
        'gq' => 'Equatorial Guinea',
        'er' => 'Eritrea',
        'ee' => 'Estonia',
        'et' => 'Ethiopia',
        'fk' => 'Falkland Islands',
        'fo' => 'Faroe Islands',
        'fj' => 'Fiji',
        'fi' => 'Finland',
        'fr' => 'France',
        'gf' => 'French Guiana',
        'pf' => 'French Polynesia',
        'tf' => 'French Southern Territories',
        'ga' => 'Gabon',
        'gm' => 'Gambia',
        'ge' => 'Georgia',
        'de' => 'Germany',
        'gh' => 'Ghana',
        'gi' => 'Gibraltar',
        'gr' => 'Greece',
        'gl' => 'Greenland',
        'gd' => 'Grenada',
        'gp' => 'Guadeloupe',
        'gu' => 'Guam',
        'gt' => 'Guatemala',
        'gg' => 'Guernsey',
        'gn' => 'Guinea',
        'gw' => 'Guinea-Bissau',
        'gy' => 'Guyana',
        'ht' => 'Haiti',
        'hm' => 'Heard Island and McDonald Islands',
        'hn' => 'Honduras',
        'hk' => 'Hong Kong',
        'hu' => 'Hungary',
        'is' => 'Iceland',
        'in' => 'India',
        'id' => 'Indonesia',
        'ir' => 'Iran',
        'iq' => 'Iraq',
        'ie' => 'Ireland',
        'im' => 'Isle of Man',
        'il' => 'Israel',
        'it' => 'Italy',
        'ci' => 'Ivory Coast',
        'jm' => 'Jamaica',
        'jp' => 'Japan',
        'je' => 'Jersey',
        'jo' => 'Jordan',
        'kz' => 'Kazakhstan',
        'ke' => 'Kenya',
        'ki' => 'Kiribati',
        'xk' => 'Kosovo',
        'kw' => 'Kuwait',
        'kg' => 'Kyrgyzstan',
        'la' => 'Laos',
        'lv' => 'Latvia',
        'lb' => 'Lebanon',
        'ls' => 'Lesotho',
        'lr' => 'Liberia',
        'ly' => 'Libya',
        'li' => 'Liechtenstein',
        'lt' => 'Lithuania',
        'lu' => 'Luxembourg',
        'mo' => 'Macao',
        'mk' => 'Macedonia',
        'mg' => 'Madagascar',
        'mw' => 'Malawi',
        'my' => 'Malaysia',
        'mv' => 'Maldives',
        'ml' => 'Mali',
        'mt' => 'Malta',
        'mh' => 'Marshall Islands',
        'mq' => 'Martinique',
        'mr' => 'Mauritania',
        'mu' => 'Mauritius',
        'yt' => 'Mayotte',
        'mx' => 'Mexico',
        'fm' => 'Micronesia',
        'md' => 'Moldova',
        'mc' => 'Monaco',
        'mn' => 'Mongolia',
        'me' => 'Montenegro',
        'ms' => 'Montserrat',
        'ma' => 'Morocco',
        'mz' => 'Mozambique',
        'mm' => 'Myanmar',
        'na' => 'Namibia',
        'nr' => 'Nauru',
        'np' => 'Nepal',
        'nl' => 'Netherlands',
        'nc' => 'New Caledonia',
        'nz' => 'New Zealand',
        'ni' => 'Nicaragua',
        'ne' => 'Niger',
        'ng' => 'Nigeria',
        'nu' => 'Niue',
        'nf' => 'Norfolk Island',
        'kp' => 'North Korea',
        'mp' => 'Northern Mariana Islands',
        'no' => 'Norway',
        'om' => 'Oman',
        'pk' => 'Pakistan',
        'pw' => 'Palau',
        'ps' => 'Palestinian Territory',
        'pa' => 'Panama',
        'pg' => 'Papua New Guinea',
        'py' => 'Paraguay',
        'pe' => 'Peru',
        'ph' => 'Philippines',
        'pn' => 'Pitcairn',
        'pl' => 'Poland',
        'pt' => 'Portugal',
        'pr' => 'Puerto Rico',
        'qa' => 'Qatar',
        'cg' => 'Republic of the Congo',
        're' => 'Reunion',
        'ro' => 'Romania',
        'ru' => 'Russia',
        'rw' => 'Rwanda',
        'bl' => 'Saint Barthelemy',
        'sh' => 'Saint Helena',
        'kn' => 'Saint Kitts and Nevis',
        'lc' => 'Saint Lucia',
        'mf' => 'Saint Martin',
        'pm' => 'Saint Pierre and Miquelon',
        'vc' => 'Saint Vincent and the Grenadines',
        'ws' => 'Samoa',
        'sm' => 'San Marino',
        'st' => 'Sao Tome and Principe',
        'sa' => 'Saudi Arabia',
        'sn' => 'Senegal',
        'rs' => 'Serbia',
        'sc' => 'Seychelles',
        'sl' => 'Sierra Leone',
        'sg' => 'Singapore',
        'sx' => 'Sint Maarten',
        'sk' => 'Slovakia',
        'si' => 'Slovenia',
        'sb' => 'Solomon Islands',
        'so' => 'Somalia',
        'za' => 'South Africa',
        'gs' => 'South Georgia and the South Sandwich Islands',
        'kr' => 'South Korea',
        'ss' => 'South Sudan',
        'es' => 'Spain',
        'lk' => 'Sri Lanka',
        'sd' => 'Sudan',
        'sr' => 'Suriname',
        'sj' => 'Svalbard and Jan Mayen',
        'sz' => 'Swaziland',
        'se' => 'Sweden',
        'ch' => 'Switzerland',
        'sy' => 'Syria',
        'tw' => 'Taiwan',
        'tj' => 'Tajikistan',
        'tz' => 'Tanzania',
        'th' => 'Thailand',
        'tg' => 'Togo',
        'tk' => 'Tokelau',
        'to' => 'Tonga',
        'tt' => 'Trinidad and Tobago',
        'tn' => 'Tunisia',
        'tr' => 'Turkey',
        'tm' => 'Turkmenistan',
        'tc' => 'Turks and Caicos Islands',
        'tv' => 'Tuvalu',
        'vi' => 'U.S. Virgin Islands',
        'ug' => 'Uganda',
        'ua' => 'Ukraine',
        'ae' => 'United Arab Emirates',
        'gb' => 'United Kingdom',
        'us' => 'United States',
        'um' => 'United States Minor Outlying Islands',
        'uy' => 'Uruguay',
        'uz' => 'Uzbekistan',
        'vu' => 'Vanuatu',
        'va' => 'Vatican',
        've' => 'Venezuela',
        'vn' => 'Vietnam',
        'wf' => 'Wallis and Futuna',
        'eh' => 'Western Sahara',
        'ye' => 'Yemen',
        'zm' => 'Zambia',
        'zw' => 'Zimbabwe'
    ];

    /**
     * Exclude countries from this list
     * Works after include countries list or is_europe flag
     * @var string[]
     */
    public static $excludeShipping = [
        'jm',
        'uz',
        'sd',
        'si',
        'lk',
        'sv',
        'pe',
        'rs',
        'sv',
        'md',
        'mh',
        'ro',
        'aw',
        'cr',
        'ec',
        'mm',
        'um',
    ];

    /**
     * Countries which not support has_battery products
     * Exclude from shipping
     * @var array
     */
    public static $excludeBatteryShipping = [
        'as',
        'gu',
        'mp',
        'pr',
        'vi',
        'um',
        'ph',
        'co',
        'ni',
        'ar',
        'pa',
        'st',
        'aw',
        'cy',
        'cz',
        'kw',
        'lt',
        'md',
        'me',
        'mh',
        'pl',
        'rs',
        'sa',
        'sv',
        'ua',
        'cl',
        'ee',
        'hu',
        'ky',
        'lk',
        'lv',
        'pe',
        'ru',
        'sd',
        'si',
        'uz',
        'cr',
        'ec',
        'mm',
        'dj',
        'ly',
        'mn',
        'mr',
        'mv',
        'nc',
        'pf',
        'tn',
        'ad',
        'bj',
        'bo',
        'cm',
        'cf',
        'td',
        'km',
        'ws',
        'cg',
        'fj',
        'bw',
        'sr',
        'kp',
        've'
    ];

    /**
     * Countries list available to select on frontend for shipping
     * If flag is_europe ignore this list
     * @var array
     */
    public static $includeShipping = [
        'ae',
        'at',
        'au',
        'be',
        'br',
        'ca',
        'ch',
        'cl',
        'co',
        'cy',
        'cz',
        'de',
        'dk',
        'ee',
        'es',
        'fi',
        'fr',
        'gb',
        'gr',
        'hk',
        'hr',
        'hu',
        'ie',
        'il',
        'ir',
        'it',
        'is',
        'jp',
        'kr',
        'li',
        'lt',
        'lu',
        'mc',
        'mo',
        'mt',
        'mx',
        'my',
        'nl',
        'no',
        'nz',
        'pl',
        'pr',
        'pt',
        'sa',
        'se',
        'sg',
        'sk',
        'sl',
        'tr',
        'tw',
        'us',
        'za'
    ];

    /**
     * EU countries
     * Linked from Saga: GeoConstants::$countries_eu
     */
    public static $countries_eu = [
	    //EU
	    'at', 'be', 'bg', 'cy', 'cz', 'de', 'dk', 'ee', 'es', 'fi', 'fr', 'gb', 'gr', 'hr', 'hu', 'ie', 'it', 'lt', 'lu', 'lv', 'mt', 'nl', 'pl', 'pt', 'ro', 'se', 'si', 'sk', 'li',
	    //other Europe
	    'al', 'ad', 'ba', 'ch', 'fo', 'gi', 'mc', 'mk', 'no', 'sm', 'va', 'ru', 'ua'
    ];

    public static $unsetGet = [
        'cur' => '{aff_currency}',
        'lang'  => '{lang}',
    ];

    /**
     * Generate random string
     * @param type $length
     * @param type $keyspace
     * @return type
     */
    public static function randomString($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
          $pieces [] = $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    /**
     * Generate random number with lenght
     * @param type $length
     * @return type
     */
    public static function randomNumber($length)
    {
        $result = '';

        for($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }

        return $result;
    }

    /**
     * Get culture country code
     * @param string $ip
     * @param type $countryCode
     * @return string
     */
    public static function getCultureCode(string $ip = null, $countryCode = null) : string
    {
        if (!$countryCode) {
            if ($ip) {
                $location = \Location::get($ip);
            } else {
				// default Vietnam IP on localhost
                $location = (in_array(request()->ip(), self::$localhostIps)) ? \Location::get('42.112.209.164') : \Location::get(request()->ip());
            }

            // TODO - REMOVE
            if (request()->get('_ip')) {
                $location = \Location::get(request()->get('_ip'));
            }
            $countryCode = !empty($location->countryCode) ? $location->countryCode : 'US';
        }

        $countryCode = strtolower($countryCode);

        if (!isset(static::$cultureCodes[$countryCode])) {
            logger()->error("No culture code", ['country_code' => $countryCode, 'location' => !empty($location) ? $location : null]);
        }

        return !empty($countryCode) && !empty(static::$cultureCodes[$countryCode]) ? static::$cultureCodes[$countryCode] : 'en-US';
    }

    /**
     * Get location country code
     * @param string $ip
     * @return string
     */
    public static function getLocationCountryCode(?string $ip = null) : string
    {
        if (!$ip) {
            $ip = request()->get('_ip');
            if (!$ip) {
                $ip = request()->ip();
            }
        }
        $location = \Location::get($ip);
        if ($location) {
            $countryCode = $location->countryCode;
        } else {
            $countryCode = 'us';
        }
        return $countryCode ? strtolower($countryCode) : 'us';
    }

    /**
     * Get list of shipping countries
     * @param bool $code_only
     * @param $product
     * @return array
     */
    public static function getShippingCountries(bool $code_only = false, $product): array
    {
        $is_europe_only = $product->is_europe_only ?? false;
        $countries_codes = $product->countries ?? [];
        $has_battery = $product->has_battery ?? false;
        $countries = static::prepareShippingCountriesByCodes($countries_codes, $code_only, $is_europe_only);
        // exclude shipping countries from existings
        if ($code_only) {
            $countries = array_values(array_diff($countries, static::$excludeShipping));
        } else {
            $countries = array_diff_key($countries, array_flip(static::$excludeShipping));
        }
        if ($has_battery) {
            if ($code_only) {
                $countries = array_values(array_diff($countries, static::$excludeBatteryShipping));
            } else {
                $countries = array_diff_key($countries, array_flip(static::$excludeBatteryShipping));
            }
        }
        // exclude all eu countries for virtual products
        if ($product->type == OdinProduct::TYPE_VIRTUAL) {
            if ($code_only) {
                $countries = array_values(array_diff($countries, static::$countries_eu));
            } else {
                $countries = array_diff_key($countries, array_flip(static::$countries_eu));
            }
        }
        return $countries;
    }

    /**
     * Prepare shipping countries by codes
     * @param array $countries_codes
     * @param bool $code_only
     * @param bool $is_europe_only
     * @param array $countries
     */
    public static function prepareShippingCountriesByCodes(array $countries_codes, bool $code_only, ?bool $is_europe_only): array
    {
        if ($code_only) {
            $countries = static::prepareCountriesCodesOnly($countries_codes, $is_europe_only);
        } else {
            $countries = static::prepareCountriesArray($countries_codes, $is_europe_only);
        }
        return $countries;
    }

    /**
     * Prepare countries and return array with codes
     * @param array $countries_codes
     * @param bool $is_europe_only
     * @return array
     */
    public static function prepareCountriesCodesOnly(array $countries_codes, ?bool $is_europe_only) {
        $countries = [];
        if (!$countries_codes) {
            if ($is_europe_only) {
                $countries = self::$countries_eu;
            } else {
                // select only available countries
                $countries = self::$includeShipping;
            }
        } else {
            $countries = $countries_codes;
        }
        return array_values(array_unique($countries));
    }

    /**
     * Prepare countries and return array ['code'] => Country name
     * @param array $countries_codes
     * @param bool $is_europe_only
     * @return array
     */
    public static function prepareCountriesArray(array $countries_codes, bool $is_europe_only) {
        $countries = [];
        if ($countries_codes) {
            foreach ($countries_codes as $key) {
                $countries[$key] = self::$countryCodes[$key];
            }
        } else {
            if ($is_europe_only) {
                $countries_keys = self::$countries_eu;
                foreach ($countries_keys as $key) {
                    $countries[$key] = self::$countryCodes[$key];
                }
            } else {
                $countries = [];
                // select only available countries
                foreach (self::$includeShipping as $key) {
                    $countries[$key] = self::$countryCodes[$key];
                }
            }
        }
        return $countries;
    }

    /**
     * Returns true if country is in Europe
     * Duplicated in Saga: Utils::isEUCountry()
     * @param string $country_code
     * @return bool
     */
    public static function isEUCountry(string $country_code): bool
    {
        return in_array(strtolower(trim($country_code)), static::$countries_eu);
    }

    /**
     * Return paypal currency code
     * @return string
     */
    public static function getPayPalCurrencyCode(): string
    {
        $local_currency = optional(CurrencyService::getCurrency())->code;
        if (!PayPalService::isCurrencySupported($local_currency)) {
            $local_currency = Currency::DEF_CUR;
        }
        return $local_currency;
    }

    /**
     * Get CDN URL
     * @param $set_production - set production URL
     * @return string
     */
    public static function getCdnUrl($set_production = false) {
        $env = \App::environment();

        $domain = Domain::getByName();
        if ($domain && $domain->is_own_cdn) {
            $url = 'https://cdn.'.str_replace('www.','', request()->getHost());
            return $url;
        } else {
            // hardcode: temporary replace cdn urls for two domains
            // Remove after check
            $wifibostCdn = 'https://cdn.wifiboost.tech';
            $xdroneCdn = 'https://cdn.xdronehd.pro';
            $daysightsCdn = 'https://cdn.daysights.pro';
            $smartbellCdn = 'https://cdn.smartbell.pro';
            $host = request()->getHost();
            if (stripos(' '.$host, 'wifiboost.tech')) {
                return $wifibostCdn;
            } else if (stripos(' '.$host, 'xdronehd.pro')) {
                return $xdroneCdn;
            } else if (stripos(' '.$host, 'daysights.pro')) {
                return $daysightsCdn;
            } else if (stripos(' '.$host, 'smartbell.pro')) {
                return $smartbellCdn;
            }
        }

        return ($env === 'production' || $set_production
            ? 'https://' . self::CDN_HOST_PRODUCTION
            : ($env === 'staging'
                ? 'https://' . self::CDN_HOST_STAGING
                : ''));
    }

	/**
	 * Replace URL for CDN
	 * @param type $url
	 * @return type
	 */
	public static function replaceUrlForCdn	(string $url): string
	{
        if (\App::environment() == 'production') {
            $urlReplace = self::CDN_HOST_PRODUCTION;
            $s3Url = self::S3_URL_PRODUCTION;
        } else {
            $urlReplace = self::CDN_HOST_STAGING;
            $s3Url = self::S3_URL_STAGING;
        }

        $domain = Domain::getByName();
        if ($domain && $domain->is_own_cdn) {
            $urlReplace = 'cdn.'.str_replace('www.','', request()->getHost());
        } else {
            // hardcode: temporary replace cdn urls for two domains
            // Remove after check
            $wifibostCdn = 'cdn.wifiboost.tech';
            $xdroneCdn = 'cdn.xdronehd.pro';
            $daysightsCdn = 'cdn.daysights.pro';
            $smartbellCdn = 'cdn.smartbell.pro';
            $host = request()->getHost();
            if (stripos(' '.$host, 'wifiboost.tech')) {
                $urlReplace = $wifibostCdn;
            } else if (stripos(' '.$host, 'xdronehd.pro')) {
                $urlReplace = $xdroneCdn;
            } else if (stripos(' '.$host, 'daysights.pro')) {
                $urlReplace = $daysightsCdn;
            } else if (stripos(' '.$host, 'smartbell.pro')) {
                $urlReplace = $smartbellCdn;
            }
        }

        $url = str_replace($s3Url, $urlReplace, $url);
		// cut www. from url
		$url = str_replace('www.', '', $url);
		return $url;
	}

    /**
     * Return URL get parameters string from get parameters
     * @param type $request
     * @param type $excludeParams
     * @return type
     */
    public static function getGlobalGetParameters($request = null, $excludeParams = [])
    {
        if (!$request) {
            $request = request();
        }
        $string = '';
        foreach (self::$globalGetParameters as $key => $param) {
            if (!in_array($param, $excludeParams)) {
                if ($request->get($param)) {
                    $string.= $param.'='.$request->get($param).'&';
                }
            }
        }
        // remove last ? from string
        if ($string) {
            $string = '?'.substr_replace($string , '', -1);
        }
        return $string;
    }

    /**
     * Return array params from url link
     * @param string $url
     * @return type
     */
    public static function getParamsFromUrl(string $url)
    {
        $url = parse_url($url);
        $paramsArray = null;
        $params = null;
        if (!empty($url['query'])) {
            $params = explode("&", $url['query']);
        }

        if ($params) {
            foreach ($params as $param) {
                parse_str($param, $value);
                if (is_array($value)) {
                    foreach ($value as $key => $val) {
                        $key = preg_replace('/\W/', '_', $key);
                        if ($key && $val) {
                            $paramsArray[$key] = $val;
                        }
                    }
                }
            }
        }

        return $paramsArray;
    }

    /**
     * Returns Mongo time object by timestamp
     * @param type $ts
     * @return UTCDateTime
     */
    public static function getMongoTimeFromTS($ts)
    {
        return new UTCDateTime($ts * 1000);
    }

    /**
     * Returns MongoId From Timestamp
     * @param int $ts
     * @param int $id it's used for the same timestamp
     * @return ObjectId
     */
    public static function getMongoIdFromTS(int $ts = null, int $id = 1): ObjectId {
        // Build Binary Id
        $binTs = pack('N', $ts ?? time()); // unsigned long
        $hostId = substr(md5(gethostname()), 0, 3); // 3 bit machine identifier
        $binPid = pack('n', posix_getpid()); // unsigned short
        $counter = substr(pack('N', $id), 1, 3); // Counter
        $binId = "{$binTs}{$hostId}{$binPid}{$counter}";

        // Convert to ASCII
        $id = '';
        for ($i = 0; $i < 12; $i++) {
            $id .= sprintf("%02X", ord($binId[$i]));
        }

        // Return Mongo ID
        return new ObjectId($id);
    }

    /**
     * Returns current time in milliseconds
     * @return string
     */
    public static function millitime()
    {
        $comps = explode(' ', microtime());
      // Note: Using a string here to prevent loss of precision
      // in case of "overflow" (PHP converts it to a double)
      return sprintf('%d%03d', $comps[1], $comps[0] * 1000);
    }

    /**
     * Get device
     */
    public static function getDevice(): string
    {
        $agent = new Agent();

        $device = Pixel::DEVICE_PC;

        if ($agent->isMobile()) {
            $device = Pixel::DEVICE_MOBILE;
        } else if($agent->isTablet()) {
            $device = Pixel::DEVICE_TABLET;
        }

        return $device;
    }

    /**
     * Return current domain
     */
    public static function getDomain(): string
    {
        return request()->server('SERVER_NAME');
    }

    /**
     * Unset get parameters
     * @param Request $request
     */
    public static function unsetGetParameters(Request $request)
    {
        foreach (static::$unsetGet as $key => $value) {
            $valueCoding = str_replace(['{', '}'], '','%7B'.$value.'%7D');
            if ($request->get($key) === $value || $request->get($key) === $valueCoding) {
                unset($request[$key]);
            }
        }
    }

    /**
     * Returns array of localized images
     * @return type
     */
    public static function getLocalizedImages(array $localizeImages): array
    {
        $imagesObj = AwsImage::whereIn('name', $localizeImages)->get();
        $images = [];

        foreach ($imagesObj as $image) {
            if (!empty($image->name)) {
                $images[$image->name] = [
                    'url' => !empty($image['urls'][app()->getLocale()]) ? \Utils::replaceUrlForCdn($image['urls'][app()->getLocale()]) : (!empty($image['urls']['en']) ? \Utils::replaceUrlForCdn($image['urls']['en']) : ''),
                    'title' => $image->title ?? ''
                ];
            }
        }

        return $images;
    }

    /**
     * Prepare phone for save
     * @param type $phone
     * @return string
     */
    public static function preparePhone($phone): string
    {
        $phone = preg_replace("/[^0-9]/", "", $phone);
        $phone = strval(intval($phone));
        return $phone;
    }

    /**
     * Generate page title by domain multiproduct logic
     * @param \App\Services\Domain $domain
     * @param type $product
     * @param string $phraseText
     * @return string
     */
    public static function generatePageTitle($domain, $product, ?string $copId = '', ?string $phraseText = ''): string
    {
        $title = '';
        if ((!empty($domain->is_multiproduct) || !empty($domain->is_catch_all)) && empty($copId)) {
            $title = $domain->getDisplayedName();
        } else {
            $title = $product->page_title ?? $product->product_name;
        }

        if ($phraseText) {
            $title .= ' - '.$phraseText;
        }

        return trim($title);
    }

    /**
     * Prepare card number to format `first 6 digits and last 4 digits, other digits are replaced with × symbol`
     * @param string $number
     * @param string $replaceSymbol
     * @return string
     */
    public static function prepareCardNumber(string $number, $replaceSymbol = 'x'): string
    {
        if (strlen($number) > 10) {
            $number = substr($number, 0, 6) . str_repeat($replaceSymbol, strlen($number) - 10) . substr($number, - 4);
        }
        return $number;
    }

    /**
     * Get user data from user agent
     * https://github.com/matomo-org/device-detector
     * @return array
     */
    public static function getUserAgentParseData(): array
    {
        $userAgent = utf8_encode(request()->header('user-agent'));
        $ip = request()->ip();
        $deviceType = null;
        try {
            $data = new DeviceDetector($userAgent);
            $data->parse();
            $deviceType = $data->getDeviceName();
            $browser = $data->getClient();
            $browser = $browser['name'] ?? null;
            $isBot = $data->isBot();
        } catch (\Exception $ex) {
            logger()->error($ex->getMessage(), ['ip' => $ip]);
        }

        if ($deviceType == OdinOrder::DEVICE_PHABLET) {
            $deviceType = OdinOrder::DEVICE_TABLET;
        }

        if ($deviceType === OdinOrder::DEVICE_FEATUREPHONE) {
            $deviceType = OdinOrder::DEVICE_SMARTPHONE;
        }

        if ($deviceType == OdinOrder::DEVICE_PLAYER_FULL) {
            $deviceType = OdinOrder::DEVICE_PLAYER;
        }

        if ($deviceType === OdinOrder::DEVICE_CAR_FULL) {
            $deviceType = OdinOrder::DEVICE_CAR;
        }

        if ($deviceType && !isset(OdinOrder::$devices[$deviceType])) {
            logger()->error("Wrong device type {$deviceType} - {$ip}", ['ip' => $ip, 'device' => $deviceType, 'user_agent' => $userAgent]);
        }

        return [
            'user_agent' => $userAgent ?? null,
            'device_type' => $deviceType ?? null,
            'browser' => $browser ?? null,
            'is_bot' => $isBot ?? null
        ];
    }

    /**
     * Returns country code location by IP address using ip-api.com service
     * Alternative method if MaxMind doesn't find location
     * SAGA: Utils::getLocationByIP()
     * @param type $ip
     * @return type
     */
    public static function getLocationCountryCodeByIPApi($ip) {
        $location = null;
        if ($ip) {
            //this endpoint is limited to 150 requests per minute from an IP address. If you go over this limit your IP address will be blackholed.
            $url = 'http://ip-api.com/json/' . $ip;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            if ($result) {
                $location = json_decode($result, true);
            }
        }
        $countryCode = !empty($location['countryCode']) ? $location['countryCode'] : '';
        return strtolower($countryCode);
    }

    /**
     * Generates tracking link
     * @param string $number
     * @param string $slug
     * @param bool $use_default_template
     * @return string|null
     */
    public static function generateTrackingLink($number, $slug, bool $use_default_template = false) {
        $default_template = 'https://track.aftership.com/#SLUG#/#NUMBER#';
        if ($use_default_template) {
            $template = $default_template;
        } else {
            $template = Setting::getValue('tracking_link_template', $default_template);
        }

        if ($number) {
            $link = str_replace('#NUMBER#', $number, $template);
            $link = str_replace('#SLUG#', $slug, $link);
            return $link;
        }
        return null;
    }

    /**
     * Add to select array lang attribute
     * @param array $select
     * @param string $lang
     */
    public static function addLangFieldToSelect(array $select, string $lang) {
        $select_lang = [];
        foreach ($select as $s) {
            if (strpos($s, '.en')) {
                $select_lang[] = str_replace('.en', '.'.$lang, $s);
            }
        }
        $select = array_merge($select, $select_lang);
        return $select;
    }
}
