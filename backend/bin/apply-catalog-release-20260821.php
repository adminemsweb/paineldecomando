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
$pdo = Connection::get();
$releaseMarker = 'data_20260821_star_delta_catalog';
$check = $pdo->prepare('SELECT 1 FROM schema_migrations WHERE migration = :migration LIMIT 1');
$check->execute(['migration' => $releaseMarker]);

if ($check->fetchColumn()) {
    fwrite(STDOUT, "Catálogo Estrela-Triângulo desta release já está atualizado.\n");
    exit(0);
}

$scripts = [
    'seed-star-delta-variants.php',
    'update-apr-product-content.php',
    'update-apr-product-15cv-380v.php',
    'update-apr-product-10cv-380v.php',
    'update-apr-product-10cv-220v.php',
    'update-apr-product-7-5cv-220v.php',
    'update-product-7-5cv-380v.php',
    'update-star-delta-order.php',
];

foreach ($scripts as $script) {
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/' . $script);
    passthru($command, $exitCode);
    if ($exitCode !== 0) {
        throw new RuntimeException("Falha ao executar a atualização de catálogo: {$script}");
    }
}

$record = $pdo->prepare('INSERT INTO schema_migrations (migration) VALUES (:migration)');
$record->execute(['migration' => $releaseMarker]);
fwrite(STDOUT, "Catálogo Estrela-Triângulo desta release atualizado com sucesso.\n");
