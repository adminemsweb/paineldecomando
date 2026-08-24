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
$catalogPath = BASE_PATH . '/database/catalog.json';
$raw = file_get_contents($catalogPath);
if (!is_string($raw)) throw new RuntimeException('Catálogo de produção não encontrado.');
$products = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
if (!is_array($products) || $products === []) throw new RuntimeException('Catálogo de produção vazio.');
if (preg_match('/(^|[^a-z])(APR|WEG)([^a-z]|$)/iu', $raw)) throw new RuntimeException('Catálogo contém marca antiga.');

$pdo = Connection::get();
$fields = [
    'name','slug','summary','description','features','benefits','components','voltages','power_range',
    'protection_rating','featured_image','gallery_images','video_url','video_urls','category_name',
    'reference_code','brand','model','price_cents','installments','stock_status','stock_quantity',
    'lead_time','sales_channel','warranty_days','sort_order','featured','status','seo_title','seo_description',
];
$placeholders = implode(',', array_map(static fn(string $field): string => ':' . $field, $fields));
$updates = implode(',', array_map(static fn(string $field): string => "{$field}=VALUES({$field})", array_values(array_filter($fields, static fn(string $field): bool => $field !== 'slug'))));
$statement = $pdo->prepare('INSERT INTO products (' . implode(',', $fields) . ',published_at) VALUES (' . $placeholders . ',CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE ' . $updates . ',published_at=CURRENT_TIMESTAMP,deleted_at=NULL,updated_at=CURRENT_TIMESTAMP');

$pdo->beginTransaction();
try {
    $slugs = [];
    foreach ($products as $product) {
        if (!is_array($product)) throw new RuntimeException('Produto inválido no catálogo.');
        $values = [];
        foreach ($fields as $field) $values[$field] = $product[$field] ?? null;
        $statement->execute($values);
        $slugs[] = (string)$values['slug'];
    }
    $quoted = implode(',', array_fill(0, count($slugs), '?'));
    $archive = $pdo->prepare("UPDATE products SET status='archived',updated_at=CURRENT_TIMESTAMP WHERE slug NOT IN ({$quoted}) AND deleted_at IS NULL");
    $archive->execute($slugs);
    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    throw $exception;
}
fwrite(STDOUT, 'Produtos sincronizados: ' . count($products) . PHP_EOL);

