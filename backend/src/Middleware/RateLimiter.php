<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Config\Env;
use App\Core\Logger;
use App\Core\RequestContext;
use App\Core\Response;
use RuntimeException;

final class RateLimiter
{
    public static function enforce(string $scope, int $maximum, int $windowSeconds, ?string $client = null, bool $failClosed = false): void
    {
        $result = self::attempt($scope, $maximum, $windowSeconds, $client, $failClosed);
        header('X-RateLimit-Limit: ' . $maximum);
        header('X-RateLimit-Remaining: ' . $result['remaining']);
        if ($result['allowed']) return;

        header('Retry-After: ' . $result['retry_after']);
        Logger::security('Rate limit blocked request', [
            'scope' => $scope,
            'client_id' => $result['client_id'],
            'retry_after_seconds' => $result['retry_after'],
        ]);
        Response::error('Muitas tentativas. Aguarde alguns minutos e tente novamente.', 429);
    }

    /** @return array{allowed:bool,remaining:int,retry_after:int,client_id:string} */
    public static function attempt(string $scope, int $maximum, int $windowSeconds, ?string $client = null, bool $failClosed = false): array
    {
        if ($maximum < 1 || $windowSeconds < 1) throw new RuntimeException('Configuração de rate limit inválida.');
        $client ??= RequestContext::clientIp();
        $clientId = substr(hash('sha256', $client), 0, 12);
        $key = hash('sha256', $scope . '|' . $client);
        $directory = Env::get('RATE_LIMIT_PATH', dirname(__DIR__, 2) . '/storage/rate-limits') ?? '';
        if ($directory === '' || (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory))) {
            Logger::error('Rate limit storage unavailable', ['scope'=>$scope]);
            return ['allowed'=>!$failClosed, 'remaining'=>$failClosed ? 0 : $maximum, 'retry_after'=>$failClosed ? 60 : 0, 'client_id'=>$clientId];
        }

        $file = $directory . '/' . $key . '.json';
        $handle = fopen($file, 'c+');
        if ($handle === false || !flock($handle, LOCK_EX)) {
            if (is_resource($handle)) fclose($handle);
            Logger::error('Rate limit lock unavailable', ['scope'=>$scope]);
            return ['allowed'=>!$failClosed, 'remaining'=>$failClosed ? 0 : $maximum, 'retry_after'=>$failClosed ? 60 : 0, 'client_id'=>$clientId];
        }

        $now = time();
        $raw = stream_get_contents($handle);
        $stored = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
        $startedAt = is_array($stored) ? (int)($stored['started_at'] ?? $now) : $now;
        $count = is_array($stored) ? (int)($stored['count'] ?? 0) : 0;
        if ($now >= $startedAt + $windowSeconds) {
            $startedAt = $now;
            $count = 0;
        }
        $count++;
        rewind($handle);
        $payload = json_encode(['started_at'=>$startedAt,'count'=>$count], JSON_THROW_ON_ERROR);
        $persisted = ftruncate($handle, 0)
            && fwrite($handle, $payload) === strlen($payload)
            && fflush($handle);
        if (!$persisted) {
            flock($handle, LOCK_UN);
            fclose($handle);
            Logger::error('Rate limit state could not be persisted', ['scope'=>$scope]);
            return ['allowed'=>!$failClosed, 'remaining'=>$failClosed ? 0 : $maximum, 'retry_after'=>$failClosed ? 60 : 0, 'client_id'=>$clientId];
        }
        flock($handle, LOCK_UN);
        fclose($handle);

        $allowed = $count <= $maximum;
        return [
            'allowed' => $allowed,
            'remaining' => max(0, $maximum - $count),
            'retry_after' => $allowed ? 0 : max(1, ($startedAt + $windowSeconds) - $now),
            'client_id' => $clientId,
        ];
    }
}
