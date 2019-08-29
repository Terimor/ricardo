<?php


if (!function_exists('t'))
{
    function t($phrase, $args = []){
	return \App\Services\I18nService::getTranslatedPhrase($phrase, app()->getLocale(), $args);	
    }
}