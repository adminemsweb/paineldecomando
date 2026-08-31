<?php
declare(strict_types=1);

use App\Config\Env;
use App\Database\Connection;

define('BASE_PATH', dirname(__DIR__));
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $file = BASE_PATH . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) require $file;
});

Env::load(BASE_PATH . '/.env');
if (Env::get('DB_DRIVER') !== 'sqlite') exit(0);

$schema = file_get_contents(BASE_PATH . '/database/sqlite-schema.sql');
if (!is_string($schema) || trim($schema) === '') {
    throw new RuntimeException('Schema SQLite não encontrado.');
}

Connection::get()->exec($schema);
echo "Schema local verificado.\n";
