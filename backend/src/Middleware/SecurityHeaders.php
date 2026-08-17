<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Config\Env;

final class SecurityHeaders
{
    public static function apply(): void
    {
        header_remove('X-Powered-By');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: no-referrer');
        header("Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()");
        header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'self'");

        $path = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
        if (str_starts_with($path, '/api/v1/auth/') || str_starts_with($path, '/api/v1/admin/')) {
            header('Cache-Control: no-store, max-age=0');
            header('Pragma: no-cache');
        }

        $https = (($_SERVER['HTTPS'] ?? '') !== '' && ($_SERVER['HTTPS'] ?? '') !== 'off');
        if ($https && Env::get('APP_ENV') === 'production') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}
