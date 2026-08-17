<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Config\Env;
use App\Core\Logger;
use App\Core\Response;

final class Cors
{
    public static function handle(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowed = array_map('trim', explode(',', Env::get('CORS_ALLOWED_ORIGINS', '') ?? ''));
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($origin !== '' && !in_array($origin, $allowed, true) && !in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            Logger::security('CORS blocked origin', ['origin_hash'=>substr(hash('sha256', $origin), 0, 12),'request_method'=>$method]);
            Response::error('Origem da solicitação não autorizada.', 403);
        }
        if ($origin !== '' && in_array($origin, $allowed, true)) {
            header("Access-Control-Allow-Origin: {$origin}");
            header('Vary: Origin');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
            header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        }
        if ($method === 'OPTIONS') Response::json(['success' => true, 'data' => null], 204);
    }
}
