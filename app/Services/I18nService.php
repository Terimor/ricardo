<?php

namespace App\Services;
use App\Models\I18n;

/**
 * I18n Service class
 */
class I18nService
{
    public $translations;
    
    /**
     * Load phrases
     * @param string $category
     * @return array
     */
    public static function loadPhrases(string $category)
    {
	$lang = app()->getLocale();
	if (empty(I18n::$loadedPhrases[$lang])) {
	    $loadedPhrases = I18n::where(['categories' => $category])->pluck($lang, 'phrase')->toArray();
	    I18n::$loadedPhrases[$lang] = $loadedPhrases;
	}
	
	return I18n::$loadedPhrases[$lang];
    }

    /**
    * Get translation phrase
    * From saga I18n::t
    * @param type $phrase
    * @param type $lang
    */
   public static function getTranslatedPhrase(string $phrase, string $language = 'en', $args = []): string
   {   
	$translation = $phrase;
        $language = $language ? strtolower($language) : 'en';

	if (isset(static::$browser_codes[$language])) {
	  $language = static::$browser_codes[$language];
	}
	
	//$translated_languages = I18n::getTranslationLanguages(true);	
	$loadedPhrases = !empty(I18n::$loadedPhrases[$language]) ? I18n::$loadedPhrases[$language] : [];

	if (!empty($loadedPhrases[$translation])) {
	    $translation = $loadedPhrases[$translation];
	} else {
	    logger()->error("URGENT: `{$translation}` not found in translations. Args: ".json_encode($args));
	}	
	
	if (empty($args['shopname'])) {
	    $args['shopname'] = \Utils::getSetting('shop_name');		    
	}
	if ($phrase !== 'global.support_team' && empty($args['support_team'])) {
	    $args['support_team'] = t('global.support_team');
	}		
	
	if ($args) {
	    foreach ($args as $key => $value) {
		$placeholderKey = "#".strtoupper($key)."#";
		$translation = str_replace($placeholderKey, $value, $translation);
		if (!in_array($placeholderKey, I18n::$placeholders)) {
		    logger()->error("Non-registered placeholder {$placeholderKey}. Add it to I18n::placeholders!");		  
		}
		if ($value === null) {
		    logger()->error("Translation is null for {$placeholderKey}");
		}
	    }
	}

	$translation = str_replace("&#39;", "’", $translation);
	if (substr_count($translation, '#') > 1) {
	    $other_hashes_cnt = substr_count($translation, '/#/');
	    if (substr_count($translation, '#') != $other_hashes_cnt) {
		logger()->error("Non-translated placeholders for `{$phrase}`: {$translation}. Arguments: ". json_encode($args));	      
	    }
	}
       
	return $translation;
   }

}