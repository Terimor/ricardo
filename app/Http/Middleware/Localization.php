<?php

namespace App\Http\Middleware;

use App\Services\I18nService;
use Closure;
use Illuminate\Support\Facades\Cookie;
use App\Models\I18n;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        \Utils::unsetGetParameters($request);

        $translated_languages = I18n::getTranslationLanguages(true);

        if ($request->has('lang')) {
            $lang = $request->get('lang');
            if (!in_array($lang, $translated_languages)) {
                $lang = 'en';
            }
        } else {
            $lang = $request->getPreferredLanguage($translated_languages);
        }

        // Check for specific languages that needs to be replaced for internal usage.
        $lang = I18nService::getInternalLanguageByLocaleLanguage($lang);

        app()->setLocale($lang);
        return $next($request);
    }
}
