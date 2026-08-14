<?php
declare(strict_types=1);

namespace App\Core;

use App\Config\Env;
use ErrorException;
use Throwable;

final class ErrorHandler
{
    public static function register(): void
    {
        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) return false;
            throw new ErrorException($message, 0, $severity, $file, $line);
        });
        set_exception_handler([self::class, 'handle']);
        register_shutdown_function(static function (): void {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                self::handle(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
            }
        });
    }

    public static function handle(Throwable $exception): never
    {
        Logger::exception($exception);
        $message = Env::bool('APP_DEBUG') ? $exception->getMessage() : 'Ocorreu um erro interno.';
        Response::error($message, 500);
    }
}
