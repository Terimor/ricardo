<?php

namespace App\Http\Middleware;

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
        //$translated_languages = ['ru', 'en'];
        $translated_languages = I18n::getTranslationLanguages(true);
        
        if ($request->has('language')) {
            $lang = $request->get('language');
            if (!in_array($lang, $translated_languages)) {
                $lang = 'en';
            }
        } else {
            $lang = $request->getPreferredLanguage($translated_languages);
        }
        
        /*if($request->hasCookie('lang')) {
            app()->setLocale($lang);
            return $next($request);    
        }*/

        app()->setLocale($lang);
        return $next($request);
        
        /*$response = $next($request);
        return $response->withCookie(cookie()->forever('lang', $lang));*/
        
    }
}
