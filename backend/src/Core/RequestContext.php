<?php
declare(strict_types=1);

namespace App\Core;

final class RequestContext
{
    private static ?string $requestId = null;
    private static int $startedAt = 0;

    public static function begin(): void
    {
        self::$requestId = null;
        self::$startedAt = hrtime(true);
        self::id();
    }

    public static function id(): string
    {
        if (self::$requestId !== null) return self::$requestId;
        $incoming = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($_SERVER['HTTP_X_REQUEST_ID'] ?? '')) ?? '';
        return self::$requestId = $incoming !== '' ? substr($incoming, 0, 64) : bin2hex(random_bytes(12));
    }

    public static function durationMs(): float
    {
        if (self::$startedAt === 0) self::$startedAt = hrtime(true);
        return round((hrtime(true) - self::$startedAt) / 1_000_000, 2);
    }

    public static function clientIp(): string
    {
        $remote = self::validIp((string)($_SERVER['REMOTE_ADDR'] ?? '')) ?? 'cli';
        if ($remote === 'cli' || !self::isTrustedProxy($remote)) return $remote;

        $forwarded = (string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
        $chain = array_values(array_filter(array_map(
            static fn(string $value): ?string => self::validIp(trim($value)),
            explode(',', $forwarded),
        )));
        $chain[] = $remote;

        for ($index = count($chain) - 1; $index >= 0; $index--) {
            if (!self::isTrustedProxy($chain[$index])) return $chain[$index];
        }

        return $remote;
    }

    private static function validIp(string $value): ?string
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false ? $value : null;
    }

    private static function isTrustedProxy(string $ip): bool
    {
        $configured = \App\Config\Env::get('TRUSTED_PROXY_CIDRS', '') ?? '';
        foreach (array_filter(array_map('trim', explode(',', $configured))) as $cidr) {
            if (self::ipInCidr($ip, $cidr)) return true;
        }
        return false;
    }

    private static function ipInCidr(string $ip, string $cidr): bool
    {
        [$network, $prefix] = array_pad(explode('/', $cidr, 2), 2, null);
        $ipBinary = inet_pton($ip);
        $networkBinary = inet_pton($network);
        if ($ipBinary === false || $networkBinary === false || strlen($ipBinary) !== strlen($networkBinary)) return false;

        $bits = strlen($ipBinary) * 8;
        $prefixLength = $prefix === null ? $bits : filter_var($prefix, FILTER_VALIDATE_INT);
        if ($prefixLength === false || $prefixLength < 0 || $prefixLength > $bits) return false;

        $fullBytes = intdiv($prefixLength, 8);
        $remainingBits = $prefixLength % 8;
        if ($fullBytes > 0 && substr($ipBinary, 0, $fullBytes) !== substr($networkBinary, 0, $fullBytes)) return false;
        if ($remainingBits === 0) return true;

        $mask = (0xff << (8 - $remainingBits)) & 0xff;
        return (ord($ipBinary[$fullBytes]) & $mask) === (ord($networkBinary[$fullBytes]) & $mask);
    }
}
