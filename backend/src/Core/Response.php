<?php
declare(strict_types=1);

namespace App\Core;

final class Response
{
    /** @param array<string,mixed> $payload */
    public static function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        exit;
    }

    public static function success(mixed $data = null, string $message = 'OK', int $status = 200, ?array $meta = null): never
    {
        $body = ['success' => true, 'data' => $data, 'message' => $message];
        if ($meta !== null) $body['meta'] = $meta;
        self::json($body, $status);
    }

    /** @param array<string,list<string>> $errors */
    public static function error(string $message, int $status = 400, array $errors = []): never
    {
        self::json(['success' => false, 'data' => null, 'message' => $message, 'errors' => $errors], $status);
    }
}

