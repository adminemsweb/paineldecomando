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
$slug = 'painel-irrigacao-soft-starter-ssw07-150cv-220v';
$product = [
    'name' => 'Painel para Irrigação com Soft Starter SSW07 150CV 220V | Painel de Comando',
    'slug' => $slug,
    'summary' => 'Painel para sistemas de irrigação de grande porte, com partida suave, proteção elétrica e operação remota para motores de até 150 CV em 220 V.',
    'description' => "Este painel foi projetado especificamente para sistemas de irrigação de grande porte, oferecendo controle e proteção eficientes para motores de até 150 CV em redes de 220 V. Equipado com uma Soft Starter SSW07, proporciona partidas e paradas suaves, aumentando a vida útil do motor e reduzindo picos de corrente. A interface homem-máquina (IHM) remota e os sinaleiros facilitam a operação e a identificação do estado do sistema.\n\nO conjunto conta com disjuntor geral em caixa moldada, seccionadora na porta, proteção do circuito de comando, botão de emergência e bornes para acionamento remoto por contato seco. A caixa metálica de 80 × 60 × 30 cm oferece construção robusta para aplicações agrícolas e industriais.\n\nATENÇÃO: os botões da porta e do quadro de comando são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos é realizado por flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual.\n\nInstalação e uso técnico: este equipamento deve ser instalado e manuseado por profissionais qualificados ou com conhecimento técnico adequado. Isso garante o pleno desempenho do produto e a segurança da aplicação. O suporte pós-venda está disponível para orientações comerciais e de uso geral; dúvidas técnicas específicas sobre instalação devem ser direcionadas a profissionais habilitados da área elétrica.\n\nAo proporcionar partidas e paradas suaves, proteger contra sobrecargas e facilitar a operação remota, este painel contribui para a eficiência, a segurança e a longevidade do sistema de irrigação.",
    'features' => json_encode([
        'Marca: Painel de Comando',
        'Aplicação: sistema de irrigação',
        'Potência máxima do motor: 150 CV',
        'Tensão de operação: 220 V',
        'Alimentação: trifásica',
        'Caixa metálica: 80 × 60 × 30 cm',
        'Disjuntor geral em caixa moldada com seccionadora na porta',
        'Disjuntor para o circuito de comando',
        'Interface homem-máquina remota',
        'Sinaleiros: comando ligado, sobrecarga e motor ligado',
        'Botão de parada de emergência',
        'Soft Starter SSW07 para motor de 150 CV em 220 V',
        'Bornes para acionamento remoto por contato seco',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'benefits' => json_encode([
        'Partidas e paradas suaves para motores de alta potência',
        'Redução de trancos mecânicos e picos de corrente',
        'Proteção elétrica completa dos circuitos de potência e comando',
        'Operação remota facilitada pela IHM dedicada',
        'Identificação visual rápida das condições do sistema',
        'Maior segurança com seccionadora e parada de emergência',
        'Maior eficiência e longevidade para o sistema de irrigação',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'components' => json_encode([
        'Caixa metálica 80 × 60 × 30 cm',
        'Soft Starter SSW07 150 CV 220 V',
        'Disjuntor geral em caixa moldada',
        'Seccionadora instalada na porta',
        'Disjuntor do circuito de comando',
        'Interface homem-máquina remota',
        'Sinaleiros de estado',
        'Botão de parada de emergência',
        'Bornes para acionamento remoto',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'voltages' => '220 V trifásico',
    'power_range' => 'Até 150 CV',
    'protection_rating' => null,
    'featured_image' => '/images/painel-irrigacao-soft-starter-com-logo.png',
    'gallery_images' => '[]',
    'video_url' => '/videos/irriguiacao.mp4',
    'video_urls' => '["/videos/irriguiacao.mp4"]',
    'category_name' => 'Painel para Irrigação',
    'reference_code' => 'PAINEL-IRRI-SSW07-150CV-220V',
    'brand' => 'Painel de Comando',
    'model' => 'Painel para Irrigação',
    'price_cents' => 2662485,
    'installments' => 3,
    'stock_status' => 'in_stock',
    'stock_quantity' => 1,
    'lead_time' => 'Disponível em 3 dias úteis',
    'sales_channel' => 'both',
    'warranty_days' => 365,
    'featured' => 0,
    'status' => 'published',
    'seo_title' => 'Painel para Irrigação Soft Starter SSW07 150CV 220V | Painel de Comando',
    'seo_description' => 'Painel de Comando para irrigação com Soft Starter SSW07, motor de até 150 CV em 220 V, IHM remota e proteção elétrica completa.',
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

fwrite(STDOUT, "Produto para irrigação 150 CV 220 V atualizado.\n");
