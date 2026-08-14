<?php
declare(strict_types=1);

namespace App\Core;

final class RequestContext
{
    private static ?string $requestId = null;

    public static function id(): string
    {
        if (self::$requestId !== null) return self::$requestId;
        $incoming = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($_SERVER['HTTP_X_REQUEST_ID'] ?? '')) ?? '';
        return self::$requestId = $incoming !== '' ? substr($incoming, 0, 64) : bin2hex(random_bytes(12));
    }
}
