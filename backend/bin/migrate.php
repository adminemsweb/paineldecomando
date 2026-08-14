<?php
declare(strict_types=1);

use App\Config\Env;
use App\Database\Connection;
use App\Database\MigrationRunner;

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $file = BASE_PATH . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) require $file;
});

Env::load(dirname(BASE_PATH) . '/.env');
Env::load(BASE_PATH . '/.env');

$count = (new MigrationRunner(Connection::get(), BASE_PATH . '/database/migrations'))->run();
fwrite(STDOUT, "Migrações aplicadas: {$count}\n");
