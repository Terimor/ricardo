<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Setting;
use App\Exceptions\AuthException;
use Illuminate\Http\Request;

class ApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $req
     * @param \Closure $next
     * @return mixed
     * @throws AuthException
     */
    public function handle(Request $req, Closure $next)
    {
        $key = Setting::getValue('incoming_requests_api_key');
        if (!$key || $req->header('x-api-key') !== $key) {
            throw new AuthException();
        }
        return $next($req);
    }
}
