<?php
declare(strict_types=1);

use App\Config\Env;
use App\Database\Connection;
use App\Repositories\ProductRepository;

define('BASE_PATH', dirname(__DIR__));
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $file = BASE_PATH . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) require $file;
});

Env::load(BASE_PATH . '/.env');
$pdo = Connection::get();
$repository = new ProductRepository($pdo);
$adminId = (int)$pdo->query("SELECT id FROM users WHERE deleted_at IS NULL ORDER BY id LIMIT 1")->fetchColumn();
if ($adminId < 1) throw new RuntimeException('Nenhum usuário disponível para registrar a criação.');

$variants = [
    ['15CV 380V', 'painel-estrela-triangulo-15cv-380v', 'Até 15 CV', '380 V', 115400, 'PET-15CV-380V'],
    ['10CV 380V', 'painel-estrela-triangulo-10cv-380v', 'Até 10 CV', '380 V', 104800, 'PET-10CV-380V'],
    ['10CV 220V', 'painel-estrela-triangulo-10cv-220v', 'Até 10 CV', '220 V', 115400, 'PET-10CV-220V'],
    ['7,5CV 220V', 'painel-estrela-triangulo-7-5cv-220v', 'Até 7,5CV', '220V Trifásico', 108400, 'PAINEL-E.T-7,5CV+MAN-AUT.ECO'],
    ['7,5CV 380V', 'painel-estrela-triangulo-7-5cv-380v', 'Até 7,5CV', '380V Trifásico', 104800, 'PAINEL-E.T-7,5CV+380-MAN-AUT.ECO'],
];

$created = 0;
foreach ($variants as $index => [$variant, $slug, $power, $voltage, $price, $reference]) {
    if ($repository->slugExists($slug)) continue;
    $repository->create([
        'name' => "Painel Estrela Triângulo {$variant} Man/Aut. Eco",
        'slug' => $slug,
        'summary' => 'Partida segura para motores trifásicos com acionamento manual e automático.',
        'description' => 'Painel Estrela-Triângulo montado e testado, indicado para reduzir a corrente de partida e proteger o motor.',
        'features' => ['Acionamento manual e automático', 'Proteção contra sobrecarga', 'Montagem industrial organizada'],
        'benefits' => ['Redução da corrente de partida', 'Maior proteção do motor', 'Instalação simplificada'],
        'components' => [],
        'voltages' => $voltage,
        'power_range' => $power,
        'protection_rating' => 'IP54',
        'image_url' => '/images/painel-estrela-triangulo-15cv-principal.png',
        'gallery_images' => [],
        'video_urls' => [],
        'category_name' => 'Painéis de partida',
        'reference_code' => $reference,
        'brand' => 'Painel de Comando',
        'model' => 'Estrela-Triângulo Man/Aut. Eco',
        'price_cents' => $price,
        'installments' => 3,
        'stock_status' => 'in_stock',
        'stock_quantity' => 5,
        'lead_time' => 'Disponível em 5 dias úteis',
        'sales_channel' => 'both',
        'warranty_days' => 365,
        'sort_order' => $index + 2,
        'featured' => false,
        'status' => 'published',
        'seo_title' => "Painel Estrela Triângulo {$variant}",
        'seo_description' => 'Painel Estrela-Triângulo para motores trifásicos.',
    ], $adminId);
    $created++;
}

fwrite(STDOUT, "Variantes criadas: {$created}\n");
