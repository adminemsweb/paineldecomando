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
$sourceSlug = 'painel-inversor-cfw300-1cv-220v-mono';
$slug = 'painel-inversor-cfw300-2cv-220v-mono';
$fields = [
    'name','slug','summary','description','features','benefits','components','voltages','power_range',
    'protection_rating','featured_image','gallery_images','video_url','video_urls','category_name',
    'reference_code','brand','model','price_cents','installments','stock_status','stock_quantity',
    'lead_time','sales_channel','warranty_days','featured','status','seo_title','seo_description',
];

$source = $pdo->prepare('SELECT ' . implode(',', $fields) . ' FROM products WHERE slug = :slug LIMIT 1');
$source->execute(['slug' => $sourceSlug]);
$product = $source->fetch();
if (!is_array($product)) {
    throw new RuntimeException('Produto CFW300 de 1 CV não encontrado para reutilizar mídia e configuração.');
}

$product['name'] = 'Painel com Inversor CFW300 WEG 2CV 220V Mono | APR';
$product['slug'] = $slug;
$product['summary'] = 'Painel compacto para controle preciso de motores trifásicos de até 2 CV, com entrada monofásica 220 V e saída trifásica 220 V.';
$product['description'] = "O Painel com Inversor CFW300 WEG 2CV 220V Mono é uma solução completa para o controle de motores trifásicos com alimentação de entrada monofásica. Ideal para aplicações industriais que necessitam de ajustes precisos de velocidade e proteção do sistema, este painel é compacto, eficiente e de fácil instalação, garantindo segurança e confiabilidade nas operações.\n\nO conjunto oferece controle de velocidade por potenciômetro, comando liga/desliga, seleção do sentido de giro e parada de emergência. O disjuntor bipolar integrado aumenta a proteção contra curtos-circuitos, enquanto os cabos de entrada e saída inclusos facilitam a conexão e a instalação do equipamento.\n\nATENÇÃO: os botões da porta e do quadro de comando são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos é realizado por flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual.\n\nInstalação e uso técnico: o equipamento deve ser instalado e manuseado por profissionais qualificados ou com conhecimento técnico adequado. Isso garante o pleno desempenho do produto e a segurança da aplicação. O suporte pós-venda está disponível para orientações comerciais e de uso geral; dúvidas técnicas específicas sobre instalação devem ser direcionadas a profissionais habilitados da área elétrica.\n\nEste painel oferece equilíbrio entre performance e segurança, ajudando a otimizar processos com praticidade, eficiência e confiabilidade.";
$product['features'] = json_encode([
    'Inversor de frequência CFW300 WEG de 2 CV',
    'Entrada monofásica 220 V e saída trifásica 220 V',
    'Caixa plástica compacta e resistente de 28 × 18 × 19 cm',
    'Potenciômetro de 10 kΩ para ajuste preciso da velocidade',
    'Botão duplo liga/desliga',
    'Seletora para controle do sentido de giro do motor',
    'Disjuntor bipolar de 16 A integrado',
    'Cabo de entrada de 1 m incluso',
    'Cabo de saída de 1 m incluso',
    'Parada de emergência frontal',
], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
$product['benefits'] = json_encode([
    'Controle preciso da velocidade do motor',
    'Conversão de alimentação monofásica para saída trifásica',
    'Proteção integrada contra curtos-circuitos e sobrecargas',
    'Operação simples com comandos instalados na porta',
    'Construção compacta para instalações com espaço reduzido',
    'Cabos inclusos para facilitar a conexão ao sistema',
    'Desempenho confiável para aplicações industriais',
], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
$product['components'] = json_encode([
    'Caixa plástica 28 × 18 × 19 cm',
    'Inversor de frequência WEG CFW300 de 2 CV',
    'Potenciômetro de 10 kΩ',
    'Botão duplo liga/desliga',
    'Seletora de sentido de giro',
    'Botão de parada de emergência',
    'Disjuntor bipolar de 16 A',
    'Cabo de entrada de 1 m',
    'Cabo de saída de 1 m',
], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
$product['power_range'] = 'Até 2 CV';
$product['reference_code'] = 'PAINEL-CFW300-2CV-220V-APR';
$product['model'] = 'Painel com Inversor';
$product['price_cents'] = 283500;
$product['installments'] = 3;
$product['warranty_days'] = 90;
$product['seo_title'] = 'Painel com Inversor CFW300 WEG 2CV 220V Mono | APR';
$product['seo_description'] = 'Painel com inversor CFW300 WEG de 2 CV, entrada monofásica 220 V, saída trifásica, controle de velocidade e proteção integrada.';

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

fwrite(STDOUT, "Produto CFW300 2 CV 220 V atualizado.\n");
