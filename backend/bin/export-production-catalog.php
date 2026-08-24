<?php
declare(strict_types=1);

$sourcePath = dirname(__DIR__) . '/storage/database.sqlite';
$destinationPath = dirname(__DIR__) . '/database/catalog.json';
if (!is_file($sourcePath)) throw new RuntimeException('Banco SQLite local não encontrado.');

$source = new PDO('sqlite:' . $sourcePath, options: [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$fields = [
    'name','slug','summary','description','features','benefits','components','voltages','power_range',
    'protection_rating','featured_image','gallery_images','video_url','video_urls','category_name',
    'reference_code','brand','model','price_cents','installments','stock_status','stock_quantity',
    'lead_time','sales_channel','warranty_days','sort_order','featured','status','seo_title','seo_description',
];
$products = $source->query('SELECT ' . implode(',', $fields) . " FROM products WHERE status='published' AND deleted_at IS NULL ORDER BY sort_order,name")->fetchAll();
$serialized = json_encode($products, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
if (preg_match('/(^|[^a-z])(APR|WEG)([^a-z]|$)/iu', $serialized)) {
    throw new RuntimeException('O catálogo contém uma marca antiga e não pode ser exportado.');
}
if (file_put_contents($destinationPath, $serialized . PHP_EOL) === false) {
    throw new RuntimeException('Não foi possível gravar o catálogo de produção.');
}
fwrite(STDOUT, 'Produtos exportados: ' . count($products) . PHP_EOL);

