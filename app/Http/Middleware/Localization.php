<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cookie;

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
        $supported_languages = ['ru', 'en'];
        //$supported_languages = I18n::getSupportedLanguages();
        $lang = $request->getPreferredLanguage($supported_languages);

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
