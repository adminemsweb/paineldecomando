<?php
declare(strict_types=1);

namespace App\Core;

use App\Config\Env;
use Throwable;

final class Logger
{
    /** @param array<string,mixed> $context */
    public static function info(string $message, array $context = []): void { self::write('info', $message, $context); }
    /** @param array<string,mixed> $context */
    public static function warning(string $message, array $context = []): void { self::write('warning', $message, $context); }
    /** @param array<string,mixed> $context */
    public static function error(string $message, array $context = []): void { self::write('error', $message, $context); }

    public static function exception(Throwable $exception): void
    {
        self::error('Unhandled exception', [
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => Env::bool('APP_DEBUG') ? $exception->getTraceAsString() : null,
        ]);
    }

    /** @param array<string,mixed> $context */
    private static function write(string $level, string $message, array $context): void
    {
        $directory = Env::get('LOG_PATH', dirname(__DIR__, 2) . '/storage/logs') ?? '';
        if ($directory === '' || (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory))) {
            error_log("[{$level}] {$message}");
            return;
        }
        $record = [
            'timestamp' => date(DATE_ATOM),
            'level' => $level,
            'message' => $message,
            'request_id' => RequestContext::id(),
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            'path' => isset($_SERVER['REQUEST_URI']) ? parse_url((string)$_SERVER['REQUEST_URI'], PHP_URL_PATH) : null,
            'context' => self::redact($context),
        ];
        $encoded = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($encoded === false || file_put_contents($directory . '/app-' . date('Y-m-d') . '.jsonl', $encoded . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
            error_log("[{$level}] {$message}");
        }
    }

    private static function redact(mixed $value, ?string $key = null): mixed
    {
        if ($key !== null && preg_match('/password|secret|token|authorization|api.?code|cookie/i', $key)) return '[REDACTED]';
        if (!is_array($value)) return $value;
        $clean = [];
        foreach ($value as $itemKey => $item) $clean[$itemKey] = self::redact($item, (string)$itemKey);
        return $clean;
    }
}
