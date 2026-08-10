<?php
declare(strict_types=1);

use App\Config\Env;
use App\Core\Response;
use App\Core\Router;
use App\Middleware\Cors;

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $file = BASE_PATH . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) require $file;
});

Env::load(BASE_PATH . '/.env');
date_default_timezone_set('America/Sao_Paulo');

set_exception_handler(static function (Throwable $exception): never {
    error_log($exception->__toString());
    $message = Env::bool('APP_DEBUG') ? $exception->getMessage() : 'Ocorreu um erro interno.';
    Response::error($message, 500);
});

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
Cors::handle();

$router = new Router();
require BASE_PATH . '/routes/api.php';
require BASE_PATH . '/routes/admin.php';
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
