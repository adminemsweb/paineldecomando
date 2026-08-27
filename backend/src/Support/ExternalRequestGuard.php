<?php
declare(strict_types=1);

namespace App\Support;

use App\Config\Env;
use App\Exceptions\ServiceUnavailableException;

final class ExternalRequestGuard
{
    public static function run(callable $callback): mixed
    {
        $maximum = max(1, min(20, (int)(Env::get('EXTERNAL_MAX_CONCURRENCY', '6') ?? '6')));
        $directory = FileCache::directory('EXTERNAL_GUARD_PATH', 'external-guards');
        FileCache::ensureDirectory($directory);

        for ($slot = 0; $slot < $maximum; $slot++) {
            $handle = fopen($directory . '/slot-' . $slot . '.lock', 'c+');
            if ($handle === false) continue;
            if (!flock($handle, LOCK_EX | LOCK_NB)) { fclose($handle); continue; }
            try { return $callback(); }
            finally { flock($handle, LOCK_UN); fclose($handle); }
        }

        throw new ServiceUnavailableException('Serviços externos temporariamente ocupados. Tente novamente.');
    }
}
