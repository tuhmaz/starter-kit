<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class SecurityHeaders
{
    /**
     * رؤوس الأمان الافتراضية
     */
    protected $securityHeaders = [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), screen-wake-lock=(), accelerometer=(), gyroscope=(), magnetometer=(), midi=()',
        'Cross-Origin-Embedder-Policy' => 'require-corp',
        'Cross-Origin-Opener-Policy' => 'same-origin',
        'Cross-Origin-Resource-Policy' => 'same-origin',
    ];

    /**
     * معالجة الطلب
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // إضافة رؤوس الأمان الأساسية
        foreach ($this->securityHeaders as $header => $value) {
            $response->headers->set($header, $value);
        }

        // تكوين سياسة CSP المحسنة
        $response->headers->set('Content-Security-Policy', $this->getEnhancedCSP());

        // تحسين إعدادات ملفات تعريف الارتباط
        if ($response->headers->has('Set-Cookie')) {
            $cookies = $response->headers->getCookies();
            $response->headers->remove('Set-Cookie');

            foreach ($cookies as $cookie) {
                $response->withCookie(
                    cookie(
                        $cookie->getName(),
                        $cookie->getValue(),
                        $cookie->getExpiresTime(),
                        $cookie->getPath(),
                        $cookie->getDomain(),
                        true, // secure
                        true, // httpOnly
                        true, // raw
                        'strict' // sameSite
                    )
                );
            }
        }

        // إضافة رؤوس CORS إذا كان ضرورياً
        if ($this->shouldAllowCORS($request)) {
            $frontendUrl = config('app.frontend_url', '*');
            $response->headers->set('Access-Control-Allow-Origin', $frontendUrl);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        // إضافة HSTS في بيئة الإنتاج
        if (App::environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // إضافة رؤوس أمان إضافية
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('X-Download-Options', 'noopen');

        return $response;
    }

    /**
     * الحصول على سياسة CSP المحسنة
     */
    protected function getEnhancedCSP(): string
    {
        $csp = [
            "default-src" => ["'self'"],
            "script-src" => ["'self'", "'unsafe-inline'", "'unsafe-eval'", "https:", "http:"],
            "style-src" => ["'self'", "'unsafe-inline'", "https:", "http:"],
            "img-src" => ["'self'", "data:", "https:", "http:"],
            "font-src" => ["'self'", "data:", "https:", "http:"],
            "frame-src" => ["'self'"],
            "connect-src" => ["'self'", "wss:", "https:", "http:"],
            "media-src" => ["'self'"],
            "object-src" => ["'none'"],
            "base-uri" => ["'self'"],
            "form-action" => ["'self'"],
            "frame-ancestors" => ["'self'"],
        ];

        return $this->buildCSPString($csp);
    }

    protected function buildCSPString(array $csp): string
    {
        return implode('; ', array_map(function ($key, $values) {
            return $key . ' ' . implode(' ', $values);
        }, array_keys($csp), $csp));
    }

    /**
     * تحديد ما إذا كان يجب السماح بـ CORS للطلب
     */
    protected function shouldAllowCORS(Request $request): bool
    {
        return $request->headers->has('Origin') &&
               $request->headers->get('Origin') !== $request->getSchemeAndHttpHost();
    }

 
}
