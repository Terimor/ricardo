<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class I18n extends Model
{
    protected $collection = 'i18n';
    
    protected $dates = ['created_at', 'updated_at'];
    
    public $timestamps = true;

    public static $loadedPhrases = [];
    
    public $attributes = [
        '_id',
        'phrase',
        'fi',
        'pl',
        'my',
        'hu',
        'tr',
        'id',
        'ar',
        'ko',
        'ja',
        'no',
        'sv',
        'es',
        'fr',
        'it',
        'nl',
        'de',
        'ru',
        'en',
        'pt',
        'da',
        'cs',
        'he',
        'el',
        'br',
        'th',
        'af',
        'hi', 'bg', 'hr', 'ro', 'zh', 'ee', 'lv', 'lt', 'sk', 'mt', 'sl', 'ur', 'jv', 'bn', 'vi',
        'created_at',
        'updated_at',
    ];
    
    protected static $labels = [
        '_id'    => 'ID',
        'phrase' => 'Phrase code',
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
        'en'     => 'English',
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
    ];
        
    public static $browser_codes = [
        'ms'  => 'my',
        'pt-br' => 'br'
    ];
    
  /**
   * Returns translation languages array
   * @return type
   */
  public static function getTranslationLanguages($codes_only = false)
  {
    $langs = static::$labels;
    foreach ($langs as $lang => $name) {
      if (strlen($lang) != 2) {
        unset($langs[$lang]);
      }
    }
    if ($codes_only) {
      $langs = array_keys($langs);
    }
    return $langs;
  }
}
