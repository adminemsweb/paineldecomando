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

$pdo = Connection::get();
$slug = 'painel-inversor-cfw300-1cv-220v-mono';
$product = [
    'name' => 'Painel com Inversor CFW300 1CV 220V Mono | Painel de Comando',
    'slug' => $slug,
    'summary' => 'Painel compacto para controle preciso de motores trifásicos de até 1 CV, com entrada monofásica 220 V e saída trifásica 220 V.',
    'description' => "O Painel com Inversor CFW300 1CV 220V Mono é a solução ideal para controle de motores trifásicos com alimentação de entrada monofásica. Compacto e eficiente, proporciona controle preciso de velocidade, proteção contra sobrecarga e instalação prática para aplicações industriais que exigem segurança e desempenho.\n\nO conjunto combina alta performance, confiabilidade e facilidade de operação. O potenciômetro permite regular a velocidade do motor, enquanto o botão liga/desliga, a seletora de sentido de giro e a parada de emergência tornam o comando direto e seguro. Os cabos de entrada e saída já acompanham o produto para facilitar sua integração ao sistema.\n\nATENÇÃO: os botões da porta e do quadro de comando são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos é realizado por flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual.\n\nInstalação e uso técnico: o equipamento deve ser instalado e manuseado por profissionais qualificados ou com conhecimento técnico adequado. Isso garante o pleno desempenho do produto e a segurança da aplicação. O suporte pós-venda está disponível para orientações comerciais e de uso geral; dúvidas técnicas específicas sobre instalação devem ser direcionadas a profissionais habilitados da área elétrica.",
    'features' => json_encode([
        'Inversor de frequência CFW300 de 1 CV',
        'Entrada monofásica 220 V e saída trifásica 220 V',
        'Caixa plástica resistente e compacta de 28 × 18 × 19 cm',
        'Potenciômetro de 10 kΩ para ajuste preciso da velocidade',
        'Botão duplo liga/desliga',
        'Seletora para controle do sentido de giro do motor',
        'Disjuntor bipolar de 16 A integrado',
        'Cabo de entrada de 1 m incluso',
        'Cabo de saída de 1 m incluso',
        'Parada de emergência frontal',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'benefits' => json_encode([
        'Controle preciso da velocidade do motor',
        'Conversão de alimentação monofásica para saída trifásica',
        'Proteção integrada contra curtos-circuitos e sobrecargas',
        'Operação simples com comandos instalados na porta',
        'Construção compacta para instalações com espaço reduzido',
        'Cabos inclusos para facilitar a conexão ao sistema',
        'Maior eficiência e confiabilidade para aplicações industriais',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'components' => json_encode([
        'Caixa plástica 28 × 18 × 19 cm',
        'Inversor de frequência CFW300 de 1 CV',
        'Potenciômetro de 10 kΩ',
        'Botão duplo liga/desliga',
        'Seletora de sentido de giro',
        'Botão de parada de emergência',
        'Disjuntor bipolar de 16 A',
        'Cabo de entrada de 1 m',
        'Cabo de saída de 1 m',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'voltages' => 'Entrada 220 V monofásica / saída 220 V trifásica',
    'power_range' => 'Até 1 CV',
    'protection_rating' => null,
    'featured_image' => '/images/painel-inversor-cfw300-1cv-220v-principal.png',
    'gallery_images' => json_encode([
        '/images/painel-inversor-cfw300-1cv-220v-perspectiva.png',
        '/images/painel-inversor-cfw300-1cv-220v-frontal.png',
    ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    'video_url' => '/videos/inversorfrequencia.mp4',
    'video_urls' => json_encode(['/videos/inversorfrequencia.mp4'], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    'category_name' => 'Painel com Inversor de Frequência',
    'reference_code' => 'PAINEL-CFW300-1CV-220V',
    'brand' => 'Painel de Comando',
    'model' => 'Painel com Inversor CFW300',
    'price_cents' => 220500,
    'installments' => 3,
    'stock_status' => 'in_stock',
    'stock_quantity' => 1,
    'lead_time' => 'Disponível em 3 dias úteis',
    'sales_channel' => 'both',
    'warranty_days' => 90,
    'featured' => 0,
    'status' => 'published',
    'seo_title' => 'Painel com Inversor CFW300 1CV 220V Mono | Painel de Comando',
    'seo_description' => 'Painel com inversor CFW300 de 1 CV, entrada monofásica 220 V, saída trifásica, controle de velocidade e proteção integrada.',
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

fwrite(STDOUT, "Produto CFW300 1 CV 220 V atualizado.\n");
