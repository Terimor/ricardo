<?php

namespace App\Services;
use App\Models\Setting;

/**
 * Utils Service class
 */
class UtilsService
{
    /**
     * Default Amazon s3 URL
     */
	const S3_URL = 'odin-img-dev.s3.eu-central-1.amazonaws.com';

	public static $localhostIps = ['127.0.0.1', '192.168.1.101', '192.168.1.3'];
    /**
     * Array using global parameters on site     
     */
    public static $globalGetParameters = ['product', 'cur', 'tpl', '_ip'];

    /**
     * Culture codes (for numberFormatter)
     * Two Letter Country Code -> Culture Info Code
     * https://datahub.io/core/language-codes
     * https://support.conga.com/Reference/Supported_Culture_Codes
     */
    public static $cultureCodes = [
        'ad' => 'ca-AD',
        'ae' => 'en-AE',
        'af' => 'fa-AF',
        'ag' => 'en-AG',
        'ai' => 'en-AI',
        'al' => 'sq-AL',
        'am' => 'hy-AM',
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
     * Linked to Saga: Utils::$country_codes
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
    public static function getLocationCountryCode(string $ip = null) : string
    {
        if ($ip) {
            $location = \Location::get($ip);
        } else {
            // TODO - REMOVE _ip and Location::get('42.112.209.164')
            $location = request()->get('_ip') ? \Location::get(request()->get('_ip')) : \Location::get(request()->ip());            
        }

        return strtolower(!empty($location->countryCode) ? $location->countryCode : 'US');
    }

    /**
     * Get list of countries
     */
    public static function getCountries()
    {
        return self::$countryCodes;
    }

    /**
     * Returns true if country is in Europe
     * Duplicated in Saga: Utils::isEUCountry()
     * @param string $country_code
     * @return bool
     */
    public static function isEUCountry(string $country_code): bool
    {
	$eu = [
	    //EU
	    'at', 'be', 'bg', 'cy', 'cz', 'de', 'dk', 'ee', 'es', 'fi', 'fr', 'gb', 'gr', 'hr', 'hu', 'ie', 'it', 'lt', 'lu', 'lv', 'mt', 'nl', 'pl', 'po', 'pt', 'ro', 'se', 'si', 'sk',
	    //other Europe
	    'al', 'ad', 'ba', 'ch', 'fo', 'gi', 'mc', 'mk', 'no', 'sm', 'va'
	];
	return in_array(strtolower(trim($country_code)), $eu);
    }

    /**
     * Return paypal currency code
     * @return type
     */
    public static function getPayPalCurrencyCode()
    {
        $local_currency = optional(CurrencyService::getCurrency())->code;
        if (!in_array($local_currency, PayPalService::$supported_currencies)) {
            $local_currency = PayPalService::DEFAULT_CURRENCY;
        }

        return $local_currency;
    }

	/**
	 * Replace URL for CDN
	 * @param type $url
	 * @return type
	 */
	public static function replaceUrlForCdn	(string $url): string
	{
		$remoteHost = request()->server('HTTP_HOST');
		if (stristr(' '.$remoteHost, '127.0.0.1') || stristr(' '.$remoteHost, 'localhost') || stristr(' '.$remoteHost, '192.168.1.101') || stristr(' '.$remoteHost, '192.168.1.3')) {
			$remoteHost = Setting::getValue('cf_host_default');
		}

		$url = str_replace(self::S3_URL, 'cdn.'.$remoteHost, $url);
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
                $val = explode("=", $param);
                if (isset($val[0]) && isset($val[1])) {
                    $paramsArray[$val[0]] = $val[1];
                }
            }
        }
        
        return $paramsArray;
    }

}
