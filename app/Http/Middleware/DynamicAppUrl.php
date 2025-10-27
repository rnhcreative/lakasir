<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class DynamicAppUrl
{
    public function handle($request, Closure $next)
    {
        $host = $request->getHost();
        $scheme = $request->getScheme();
        $publicDomain = env('APP_PUBLIC_DOMAIN');

        // Jika dari IP LAN, jangan pakai HTTPS & jangan enforce domain publik
        if (preg_match('/^192\.168\./', $host) || $host === 'localhost') {
            $url = "http://{$host}" . (env('APP_PORT') ? ':' . env('APP_PORT') : '');
            Config::set('app.url', $url);
            URL::forceRootUrl($url);
            // Jangan pakai forceScheme
        } else {
            // domain publik → https
            $url = "https://{$publicDomain}";
            Config::set('app.url', $url);
            URL::forceRootUrl($url);
            URL::forceScheme('https');
        }

        return $next($request);
    }
}
