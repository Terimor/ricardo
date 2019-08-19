<?php

namespace App\Services;
use App\Models\Setting;

/**
 * Utils Service class
 */
class UtilsService
{
    
    public static $cultureCodes = [
        'RU' => 'ru-RU',
        'BY' => 'be-BY',
        'ES' => 'es-ES',
        'BR' => 'pt-BR',
        'US' => 'en-US',
        'GB' => 'en-GB',
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
     * @return string
     */
    public static function getCultureCode(string $ip = null, $countryCode = null) : string
    {
        if ($ip) {
            $location = \Location::get($ip); 
        } else {
            $location = \Location::get(request()->ip());
        }
        
        // TODO - REMOVE
        if (request()->get('_ip')) {
            $location = \Location::get(request()->get('_ip'));
        }
        if (!$countryCode) {
            $countryCode = !empty($location->countryCode) ? $location->countryCode : 'US';
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
            $location = \Location::get(request()->ip());
        }
        
        // TODO - REMOVE
        if (request()->get('_ip')) {
            $location = \Location::get(request()->get('_ip'));
        }
        return !empty($location->countryCode) ? $location->countryCode : 'US';
    }
}