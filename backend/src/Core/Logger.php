<?php
declare(strict_types=1);

namespace App\Core;

use App\Config\Env;
use Throwable;

final class Logger
{
    private const RESET = "\033[0m";
    private const COLORS = [
        'debug' => "\033[90m",
        'info' => "\033[36m",
        'success' => "\033[32m",
        'warning' => "\033[33m",
        'error' => "\033[31m",
        'security' => "\033[35m",
        'request' => "\033[34m",
    ];

    /** @param array<string,mixed> $context */
    public static function debug(string $message, array $context = []): void { self::write('debug', $message, $context); }
    /** @param array<string,mixed> $context */
    public static function info(string $message, array $context = []): void { self::write('info', $message, $context); }
    /** @param array<string,mixed> $context */
    public static function success(string $message, array $context = []): void { self::write('success', $message, $context); }
    /** @param array<string,mixed> $context */
    public static function warning(string $message, array $context = []): void { self::write('warning', $message, $context); }
    /** @param array<string,mixed> $context */
    public static function error(string $message, array $context = []): void { self::write('error', $message, $context); }
    /** @param array<string,mixed> $context */
    public static function security(string $message, array $context = []): void { self::write('security', $message, $context); }

    public static function request(int $status): void
    {
        $level = $status >= 500 ? 'error' : ($status >= 400 ? 'warning' : ($status >= 300 ? 'info' : 'success'));
        self::write('request', 'HTTP request completed', [
            'status' => $status,
            'result' => $level,
            'duration_ms' => RequestContext::durationMs(),
        ], self::statusColor($status));
    }

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
    private static function write(string $level, string $message, array $context, ?string $consoleColor = null): void
    {
        $cleanContext = self::redact($context);
        $directory = Env::get('LOG_PATH', dirname(__DIR__, 2) . '/storage/logs') ?? '';
        $record = [
            'timestamp' => date(DATE_ATOM),
            'level' => $level,
            'message' => $message,
            'request_id' => RequestContext::id(),
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            'path' => isset($_SERVER['REQUEST_URI']) ? parse_url((string)$_SERVER['REQUEST_URI'], PHP_URL_PATH) : null,
            'context' => $cleanContext,
        ];
        $encoded = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($directory !== '' && (is_dir($directory) || mkdir($directory, 0775, true) || is_dir($directory)) && $encoded !== false) {
            file_put_contents($directory . '/app-' . date('Y-m-d') . '.jsonl', $encoded . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
        if (Env::bool('LOG_CONSOLE', Env::get('APP_ENV', 'development') !== 'production')) {
            self::console($level, $message, $cleanContext, $consoleColor);
        }
    }

    private static function redact(mixed $value, ?string $key = null): mixed
    {
        if ($key !== null && preg_match('/password|secret|token|authorization|api.?code|cookie|smtp|email|phone|address/i', $key)) return '[REDACTED]';
        if (is_string($value)) return strlen($value) > 1000 ? substr($value, 0, 1000) . '…' : $value;
        if (!is_array($value)) return $value;
        $clean = [];
        foreach ($value as $itemKey => $item) $clean[$itemKey] = self::redact($item, (string)$itemKey);
        return $clean;
    }

    /** @param array<string,mixed> $context */
    private static function console(string $level, string $message, array $context, ?string $color): void
    {
        $contextJson = $context === [] ? '' : ' ' . (json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}');
        $safeText = preg_replace('/[\x00-\x1F\x7F\x1B]+/u', ' ', $message . $contextJson) ?? 'Log indisponível';
        $label = strtoupper($level);
        $line = sprintf('[%s] [%-8s] [%s] %s', date('H:i:s'), $label, RequestContext::id(), $safeText);
        $useColors = Env::bool('LOG_COLORS', true);
        $output = $useColors ? ($color ?? self::COLORS[$level] ?? '') . $line . self::RESET : $line;
        if (defined('STDERR')) fwrite(STDERR, $output . PHP_EOL);
        else error_log($output);
    }

    private static function statusColor(int $status): string
    {
        return $status >= 500 ? self::COLORS['error'] : ($status >= 400 ? self::COLORS['warning'] : ($status >= 300 ? self::COLORS['info'] : self::COLORS['success']));
    }
}
