<?php
declare(strict_types=1);

use App\Config\Env;
use App\Database\Connection;

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
    spl_autoload_register(static function (string $class): void {
        $prefix = 'App\\';
        if (!str_starts_with($class, $prefix)) return;
        $file = BASE_PATH . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) require $file;
    });
    Env::load(BASE_PATH . '/.env');
}

$pdo ??= Connection::get();
$slug = 'painel-soft-starter-ssw07-61a-20cv-220v';
$product = [
    'name' => 'Painel Soft Starter SSW07 61A 20CV 220V Trifásico M/O/A',
    'slug' => $slug,
    'summary' => 'Painel robusto com soft starter de 61 A para partida suave e proteção de motores trifásicos de até 20 CV em 220 V, inclusive em aplicações pesadas.',
    'description' => "O Painel Soft Starter SSW07 61A 20CV 220V Trifásico M/O/A oferece controle de partida suave e eficiente para motores trifásicos em aplicações pesadas. Desenvolvido para ambientes industriais exigentes, ajuda a proteger o motor e a prolongar sua vida útil por meio de partidas controladas com menor impacto elétrico e mecânico.\n\nO conjunto reúne proteção contra sobrecargas e curtos-circuitos, seletor de três posições, parada de emergência, monitoramento digital e ventilação interna. Os bornes para acionamento remoto facilitam a integração com sistemas de automação, enquanto os sinaleiros permitem identificar rapidamente as condições de operação e sobrecarga.\n\nATENÇÃO: os botões da porta e do quadro de comando são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos é realizado por flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual.\n\nInstalação e uso técnico: o equipamento deve ser instalado e manuseado por profissionais qualificados ou com conhecimento técnico adequado. Isso garante o desempenho do produto e a segurança da aplicação. O suporte pós-venda está disponível para orientações comerciais e de uso geral; dúvidas específicas de instalação devem ser direcionadas a um profissional habilitado da área elétrica.",
    'features' => json_encode([
        'Produto: painel com soft starter para aplicação pesada',
        'Modelo do soft starter: SSW07',
        'Corrente nominal: 61 A',
        'Potência máxima do motor: 20 CV',
        'Tensão de operação: 220 V',
        'Alimentação: trifásica',
        'Caixa de aço carbono: 40 × 30 × 25 cm',
        'Seletor de três posições: liga / desligado / automático',
        'Amperímetro e voltímetro digitais',
        'Bornes para acionamento remoto',
        'Kit de ventilação: 12 × 12 cm',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'benefits' => json_encode([
        'Partida eficiente com menor impacto mecânico e elétrico',
        'Proteção contra sobrecargas e curtos-circuitos',
        'Monitoramento detalhado dos parâmetros elétricos',
        'Construção resistente para ambientes industriais',
        'Operação manual ou automática conforme a aplicação',
        'Integração simplificada com sistemas de automação',
        'Ventilação interna para ajudar a prevenir superaquecimento',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'components' => json_encode([
        'Caixa de aço carbono 40 × 30 × 25 cm',
        'Mini disjuntor tripolar de 80 A',
        'Soft starter SSW07 de 61 A para aplicação pesada',
        'Seletor de três posições',
        'Botão e placa de emergência',
        'Amperímetro e voltímetro digitais',
        'Sinaleiros vermelho e amarelo para operação e sobrecarga',
        'Bornes para acionamento remoto',
        'Kit de ventilação 12 × 12 cm',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'voltages' => '220 V trifásico',
    'power_range' => 'Até 20 CV',
    'protection_rating' => null,
    'featured_image' => '/images/painel-soft-starter-ssw07-85a-30cv-380v-fechado.png',
    'gallery_images' => '["/images/painel-soft-starter-ssw07-85a-30cv-380v-aberto-frontal.png","/images/painel-soft-starter-ssw07-85a-30cv-380v-aberto-perspectiva.png"]',
    'video_url' => '/videos/softstarter.mp4',
    'video_urls' => '["/videos/softstarter.mp4"]',
    'category_name' => 'Painel com Soft Starter',
    'reference_code' => 'PAINEL-SSW07-61A-20CV220',
    'brand' => 'Painel de Comando',
    'model' => 'Painel Soft Starter',
    'price_cents' => 756000,
    'installments' => 3,
    'stock_status' => 'in_stock',
    'stock_quantity' => 1,
    'lead_time' => 'Disponível em 3 dias úteis',
    'sales_channel' => 'both',
    'warranty_days' => 365,
    'featured' => 0,
    'status' => 'published',
    'seo_title' => 'Painel Soft Starter SSW07 61A 20CV 220V M/O/A | Painel de Comando',
    'seo_description' => 'Painel Soft Starter SSW07 de 61 A para motor de até 20 CV em 220 V trifásico, com controle manual e automático.',
];

$fields = array_keys($product);
$find = $pdo->prepare('SELECT id FROM products WHERE slug = :slug LIMIT 1');
$find->execute(['slug' => $slug]);
$productId = $find->fetchColumn();

if ($productId) {
    $assignments = implode(', ', array_map(static fn(string $field): string => "{$field} = :{$field}", $fields));
    $update = $pdo->prepare("UPDATE products SET {$assignments}, published_at = COALESCE(published_at, CURRENT_TIMESTAMP), updated_at = CURRENT_TIMESTAMP, deleted_at = NULL WHERE id = :id");
    $update->execute([...$product, 'id' => $productId]);
} else {
    $product['sort_order'] = (int)$pdo->query('SELECT COALESCE(MAX(sort_order), 0) FROM products')->fetchColumn() + 1;
    $insertFields = array_keys($product);
    $placeholders = array_map(static fn(string $field): string => ':' . $field, $insertFields);
    $insert = $pdo->prepare('INSERT INTO products (' . implode(',', $insertFields) . ',published_at) VALUES (' . implode(',', $placeholders) . ',CURRENT_TIMESTAMP)');
    $insert->execute($product);
}

fwrite(STDOUT, "Produto Soft Starter 61 A 20 CV atualizado.\n");
