<?php
declare(strict_types=1);

namespace App\Config;

final class Env
{
    /** @var array<string,string> */
    private static array $values = [];

    public static function load(string $file): void
    {
        if (!is_file($file)) return;
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            self::$values[$key] = trim($value, "\"'");
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($value !== false && $value !== null) return (string)$value;
        if (array_key_exists($key, self::$values)) return self::$values[$key];

        $fileKey = $key . '_FILE';
        $file = $_ENV[$fileKey] ?? $_SERVER[$fileKey] ?? getenv($fileKey);
        if (($file === false || $file === null) && array_key_exists($fileKey, self::$values)) {
            $file = self::$values[$fileKey];
        }
        if (is_string($file) && $file !== '' && is_readable($file)) {
            return rtrim((string)file_get_contents($file), "\r\n");
        }

        return $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        return filter_var(self::get($key, $default ? 'true' : 'false'), FILTER_VALIDATE_BOOL);
    }
}
