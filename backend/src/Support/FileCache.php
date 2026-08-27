<?php
declare(strict_types=1);

namespace App\Support;

use App\Config\Env;
use App\Exceptions\ServiceUnavailableException;

final class FileCache
{
    public static function remember(string $namespace, string $key, int $ttlSeconds, callable $callback): mixed
    {
        if ($ttlSeconds < 1) return $callback();
        $directory = self::directory('CACHE_PATH', 'cache') . '/' . self::slug($namespace);
        self::ensureDirectory($directory);
        $hash = hash('sha256', $key);
        $cacheFile = $directory . '/' . $hash . '.json';
        $lockFile = $directory . '/' . $hash . '.lock';

        $cached = self::read($cacheFile);
        if ($cached !== null) return $cached;

        $handle = fopen($lockFile, 'c+');
        if ($handle === false) throw new ServiceUnavailableException('Serviço temporariamente indisponível. Tente novamente.');
        $locked = false;
        for ($attempt = 0; $attempt < 20; $attempt++) {
            if (flock($handle, LOCK_EX | LOCK_NB)) { $locked = true; break; }
            usleep(50000);
        }
        if (!$locked) {
            fclose($handle);
            throw new ServiceUnavailableException('Serviço temporariamente ocupado. Tente novamente.');
        }

        try {
            $cached = self::read($cacheFile);
            if ($cached !== null) return $cached;
            $value = $callback();
            $payload = json_encode(['expires_at'=>time() + $ttlSeconds, 'value'=>$value], JSON_THROW_ON_ERROR);
            if (file_put_contents($cacheFile, $payload, LOCK_EX) === false) {
                throw new ServiceUnavailableException('Não foi possível armazenar a resposta do serviço.');
            }
            return $value;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private static function read(string $file): mixed
    {
        if (!is_file($file)) return null;
        $decoded = json_decode((string)file_get_contents($file), true);
        if (!is_array($decoded) || (int)($decoded['expires_at'] ?? 0) <= time() || !array_key_exists('value', $decoded)) return null;
        return $decoded['value'];
    }

    public static function directory(string $envKey, string $suffix): string
    {
        return Env::get($envKey, dirname(__DIR__, 2) . '/storage/runtime/' . $suffix) ?? '';
    }

    public static function ensureDirectory(string $directory): void
    {
        if ($directory === '' || (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory))) {
            throw new ServiceUnavailableException('Armazenamento de proteção temporariamente indisponível.');
        }
    }

    private static function slug(string $value): string
    {
        return preg_replace('/[^a-z0-9_-]/', '-', strtolower($value)) ?: 'default';
    }
}
