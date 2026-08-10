<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Config\Env;
use App\Core\Response;

final class Cors
{
    public static function handle(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowed = array_map('trim', explode(',', Env::get('CORS_ALLOWED_ORIGINS', '') ?? ''));
        if ($origin !== '' && in_array($origin, $allowed, true)) {
            header("Access-Control-Allow-Origin: {$origin}");
            header('Vary: Origin');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') Response::json(['success' => true, 'data' => null], 204);
    }
}

