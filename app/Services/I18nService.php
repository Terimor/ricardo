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
    public function loadPhrases(string $category)
    {
	$lang = app()->getLocale();
	if (empty(I18n::$loadedPhrases[$lang])) {
	    $loadedPhrases = I18n::where(['categories' => $category])->pluck($lang);
	    I18n::$loadedPhrases[$lang] = $loadedPhrases;
	}
	
	return I18n::$loadedPhrases[$lang];
    }
      
    /**
    * Get translation phrase
    * @param type $phrase
    * @param type $lang
    */
   public function getTranslatedPhrase($phrase, $lang = 'en')
   {      
       $phrases = I18n::$loadedPhrases;
echo '<pre>'; var_dump($phrase); echo '</pre>'; exit;
       if (in_array(trim($phrase), $phrases)) {
	   echo '123'; exit;
       }
       echo '<pre>'; var_dump($phrases); echo '</pre>'; exit;

       return $phrase;
   }

}