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

$order = [
    'painel-estrela-triangulo' => 1,
    'painel-estrela-triangulo-15cv-380v' => 2,
    'painel-estrela-triangulo-10cv-380v' => 3,
    'painel-estrela-triangulo-10cv-220v' => 4,
    'painel-estrela-triangulo-7-5cv-220v' => 5,
    'painel-estrela-triangulo-7-5cv-380v' => 6,
];

$pdo->beginTransaction();
try {
    $statement = $pdo->prepare('UPDATE products SET sort_order = :sort_order, updated_at = CURRENT_TIMESTAMP WHERE slug = :slug AND deleted_at IS NULL');
    foreach ($order as $slug => $sortOrder) {
        $statement->execute(['sort_order' => $sortOrder, 'slug' => $slug]);
        if ($statement->rowCount() !== 1) {
            throw new RuntimeException("Produto não encontrado para ordenar: {$slug}");
        }
    }
    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    throw $exception;
}

fwrite(STDOUT, "Ordem dos seis produtos Estrela-Triângulo atualizada.\n");
