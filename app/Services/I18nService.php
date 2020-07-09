<?php

namespace App\Services;
use App\Models\I18n;
use App\Models\Setting;
use Cache;

/**
 * I18n Service class
 */
class I18nService
{
    public $translations;

    /**
     * Load phrases
     * @param string $category
     * @param bool|null $force
     * @return array
     */
    public static function loadPhrases(string $category, ?bool $force = false)
    {
        $language = app()->getLocale();
        if (empty(I18n::$loadedPhrases[$language]) || $force) {
            $cacheKey = str_replace('_', '', "I18N{$category}{$language}");
            $loadedPhrases = Cache::get($cacheKey);
            if (!$loadedPhrases) {
                $categories = [$category];
                if (strpos($category, '_page')) {
                    $categories[] = 'global_page';
                }
                $phrases = I18n::getPhrases($categories, $language);

                $loadedPhrases = static::preparePhrases($phrases, $category, $language);

                Cache::put($cacheKey, $loadedPhrases, 600);
            }

            // returns array with key [$lang]
            I18n::$loadedPhrases = $loadedPhrases;
        }

        return I18n::$loadedPhrases[$language];
    }

    /**
     * Prepare loaded phrases to cache
     * Returns array with language key, for example ['de'] => [phrases array]
     * @param $phrases
     * @param string $category
     * @param string $language
     * @return array
     */
    public static function preparePhrases($phrases, string $category, string $language): array
    {
        $loadedPhrases = [];
        $issetCategory = false;
        // generate array of en and lang values
        foreach ($phrases as $phrase) {
            if (!$phrase->en) {
                logger()->error("Empty EN phrase", ['phrase' => $phrase->phrase]);
            }
            if ($language != 'en') {
                $loadedPhrases[$language][$phrase->phrase] = !empty($phrase->$language) ? $phrase->$language : $phrase->en;
            } else {
                $loadedPhrases['en'][$phrase->phrase] = $phrase->en;
            }

            // check if one phrase isset for this category
            if (!$issetCategory && in_array($category, $phrase->categories)) {
                $issetCategory = true;
            }
        }

        if (empty($loadedPhrases[$language])) {
            logger()->error('Loaded phrases is empty', ['language' => $language, 'category' => $category]);
            $loadedPhrases[$language] = [];
        }

        if (!$issetCategory) {
            logger()->error('0 phrases for category ' . $category);
        }
        return $loadedPhrases;
    }

    /**
     * Get translation phrase virtual variable
     * From saga I18n::t but uses virtual variable I18n::$loadedPhrases
     * @param string $phrase
     * @param string $language
     * @param array $args
     * @return string
     */
    public static function getTranslatedPhrase(string $phrase, string $language, array $args = []): string
    {
        $translation = $phrase;
        $language = strtolower($language);

        if (isset(I18n::$browser_codes[$language])) {
            $language = I18n::$browser_codes[$language];
        }

        $loadedPhrases = !empty(I18n::$loadedPhrases[$language]) ? I18n::$loadedPhrases[$language] : (!empty(I18n::$loadedPhrases['en']) ? I18n::$loadedPhrases['en'] : []);

        if (!empty($loadedPhrases[$translation])) {
            $translation = $loadedPhrases[$translation];
        } else {
            // return empty string instead of code if no EN translation for this phrase in Odin
            logger()->error("URGENT: `{$translation}` not found in translations. Args: " . json_encode($args));
            $translation = '';
        }

        if ($translation) {
            if ($args) {
                foreach ($args as $key => $value) {
                    $placeholderKey = "#" . strtoupper($key) . "#";
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
                    logger()->error("Non-translated placeholders for `{$phrase}`: {$translation}. Arguments: " . json_encode($args));
                }
            }
        }

        return $translation;
    }

    /**
     * Returns language by locale
     *
     * @param string $language_code
     * @return string
     */
    public static function getInternalLanguageByLocaleLanguage(string $language_code): string
    {
        return !empty(I18n::$browser_codes[$language_code]) ? I18n::$browser_codes[$language_code] : $language_code;
    }

    /**
     * Translates the phrase
     * @param string $phrase
     * @param string $language
     * @param array $args
     * @return string
     */
    public static function t(string $phrase, string $language = 'en', array $args = []): string
    {
        $translation = I18n::getTranslationByPhraseAndLanguage($phrase, $language);

        if (!$translation) {
            logger()->warning("URGENT: `{$phrase}` not found in translations", ['args' => $args]);
        }

        if (empty($args['shopname'])) {
            $args['shopname'] = Setting::getValue('shop_name');
        }

        if ($phrase !== 'global.support_team' && empty($args['support_team'])) {
            $args['support_team'] = self::t('global.support_team', $language);
        }

        if ($args) {
            foreach ($args as $key => $value) {
                $placeholderKey = "#".strtoupper($key)."#";
                $translation = str_replace($placeholderKey, $value, $translation);
                if (!in_array($placeholderKey, I18n::$placeholders)) {
                    logger()->warning("Non-registered placeholder {$placeholderKey}. Add it to I18n::placeholders!");
                }
                if ($value === null) {
                    logger()->warning('I18nPlaceholderNull', [$placeholderKey, $phrase, $language, $args]);
                }
            }
        }

        $translation = str_replace("&#39;", "’", $translation);
        if (substr_count($translation, '#') > 1) {
            $other_hashes_cnt = substr_count($translation, '/#/');
            if (substr_count($translation, '#') != $other_hashes_cnt) {
                logger()->warning('NonTranslatedPlaceholders', [$phrase, $translation, $args]);
            }
        }
        return $translation;
    }
}
