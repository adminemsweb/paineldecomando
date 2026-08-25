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
$slug = 'painel-soft-starter-ssw07-85a-30cv-380v';
$gallery = [
    '/images/painel-soft-starter-ssw07-85a-30cv-380v-aberto-frontal.png',
    '/images/painel-soft-starter-ssw07-85a-30cv-380v-aberto-perspectiva.png',
];

$product = [
    'name' => 'Painel Soft Starter SSW07 85A 30CV 380V Trifásico M/O/A',
    'slug' => $slug,
    'summary' => 'Quadro de comando para partida e parada suave de motores trifásicos de até 30 CV em 380 V, com seletor manual, desligado e automático.',
    'description' => "O Painel Soft Starter SSW07 85A 30CV 380V Trifásico M/O/A é um quadro de comando robusto, desenvolvido para o acionamento e a proteção de motores elétricos de média potência. Com corrente nominal de 85 A, atende motores de até 30 CV em sistemas trifásicos de 380 V e oferece seletor manual, desligado e automático para maior flexibilidade operacional.\n\nA partida e a parada suaves reduzem picos de corrente e choques mecânicos, contribuindo para aumentar a vida útil do motor e do conjunto acionado. O sistema também oferece proteção contra condições como sobrecarga, falta de fase e flutuações de tensão.\n\nATENÇÃO: os botões da porta e do quadro de comando são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos é realizado por flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual.\n\nInstalação e uso técnico: este equipamento deve ser instalado e manuseado por profissionais qualificados ou com conhecimento técnico adequado. Isso garante o desempenho do produto e a segurança da aplicação. O suporte pós-venda está disponível para orientações comerciais e de uso geral; dúvidas específicas de instalação devem ser direcionadas a um profissional habilitado da área elétrica.",
    'features' => json_encode([
        'Produto: painel com soft starter',
        'Modelo do soft starter: SSW07',
        'Corrente nominal: 85 A',
        'Potência máxima do motor: 30 CV',
        'Tensão de operação: 380 V',
        'Alimentação: trifásica',
        'Seletor manual / desligado / automático (M/O/A)',
        'Função: partida e parada suave do motor e proteção elétrica',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'benefits' => json_encode([
        'Reduz picos de corrente durante a partida',
        'Diminui choques mecânicos na aceleração e desaceleração',
        'Ajuda a prolongar a vida útil do motor e do sistema acionado',
        'Adequado para motores industriais e sistemas de bombeamento de médio porte',
        'Proteção contra sobrecarga, falta de fase e flutuações de tensão',
        'Operação flexível nos modos manual, desligado e automático',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'components' => json_encode([
        'Soft starter SSW07 de 85 A',
        'Quadro de comando metálico',
        'Seletor de três posições M/O/A',
        'Sinaleiros de operação',
        'Botão de parada de emergência',
        'Proteções elétricas do circuito',
        'Flanges de encaixe rápido para os contatos da porta',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'voltages' => '380 V trifásico',
    'power_range' => 'Até 30 CV',
    'protection_rating' => null,
    'featured_image' => '/images/painel-soft-starter-com-logo.png',
    'gallery_images' => json_encode($gallery, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    'video_url' => '/videos/softstarter.mp4',
    'video_urls' => '["/videos/softstarter.mp4"]',
    'category_name' => 'Painel com Soft Starter',
    'reference_code' => 'PAINEL-SSW07-30CV380',
    'brand' => 'Painel de Comando',
    'model' => 'Painel Soft Starter',
    'price_cents' => 897750,
    'installments' => 3,
    'stock_status' => 'in_stock',
    'stock_quantity' => 1,
    'lead_time' => 'Disponível em 3 dias úteis',
    'sales_channel' => 'both',
    'warranty_days' => 365,
    'featured' => 0,
    'status' => 'published',
    'seo_title' => 'Painel Soft Starter SSW07 85A 30CV 380V M/O/A | Painel de Comando',
    'seo_description' => 'Painel Soft Starter SSW07 de 85 A para motor de até 30 CV em 380 V trifásico, com seletor manual, desligado e automático.',
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
    $maxOrder = (int)$pdo->query('SELECT COALESCE(MAX(sort_order), 0) FROM products')->fetchColumn();
    $product['sort_order'] = $maxOrder + 1;
    $insertFields = array_keys($product);
    $placeholders = array_map(static fn(string $field): string => ':' . $field, $insertFields);
    $insert = $pdo->prepare('INSERT INTO products (' . implode(',', $insertFields) . ',published_at) VALUES (' . implode(',', $placeholders) . ',CURRENT_TIMESTAMP)');
    $insert->execute($product);
}

fwrite(STDOUT, "Produto Soft Starter 30 CV atualizado.\n");
