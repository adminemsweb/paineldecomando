<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    /** @return array<string,mixed> */
    public function body(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $data = json_decode(file_get_contents('php://input') ?: '{}', true);
            return is_array($data) ? $data : [];
        }
        return $_POST;
    }

    /** @return array<string,string> */
    public function query(): array
    {
        return array_map(static fn($v) => is_string($v) ? trim($v) : '', $_GET);
    }

    public function cookie(string $name): string
    {
        $value = $_COOKIE[$name] ?? '';
        return is_string($value) ? $value : '';
    }
}
