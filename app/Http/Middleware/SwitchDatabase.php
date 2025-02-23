<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;

class SwitchDatabase
{

    public function handle($request, Closure $next)
    {
        // تحقق من وجود country في الجلسة أولاً
        $selectedCountry = $request->input('country', session('country', 'jo'));

        // تجنب تغيير الاتصال إذا كان نفس القيمة الحالية
        if (Config::get('database.default') !== $selectedCountry) {
            switch ($selectedCountry) {
                case 'sa':
                case 'eg':
                case 'ps':
                    Config::set('database.default', $selectedCountry);
                    Config::set('cache.default', $selectedCountry . '_redis');
                    session(['country' => $selectedCountry]);
                    break;
                default:
                    Config::set('database.default', 'jo');
                    Config::set('cache.default', 'jo_redis');
                    session(['country' => 'jo']);
                    break;
            }
        }

        // تكوين Redis لكل دولة
        $redisPrefix = $selectedCountry . ':';
        Config::set('database.redis.' . $selectedCountry . '_redis', [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'prefix' => $redisPrefix,
        ]);

        return $next($request);
    }
}
