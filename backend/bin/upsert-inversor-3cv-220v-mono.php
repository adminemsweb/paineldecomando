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
$slug = 'painel-inversor-3cv-220v-mono';
$product = [
    'name' => 'Painel com Inversor 3CV 220V Mono | Painel de Comando',
    'slug' => $slug,
    'summary' => 'Painel IP65 para controle de motores trifásicos de 0,5 CV a 3 CV, com entrada monofásica, saída trifásica 220 V e IHM remota.',
    'description' => "O Painel Completo com Inversor de Frequência Painel de Comando de 3CV é uma excelente opção para quem busca eficiência e segurança no controle de motores trifásicos de 0,5CV até 3CV. Com entrada monofásica e saída trifásica, proporciona controle preciso e confiável, além de operação simples por meio da IHM remota, potenciômetro de 10 kΩ, botão duplo liga/desliga e parada de emergência.\n\nA IHM remota facilita o monitoramento e o ajuste dos parâmetros. O disjuntor bipolar de 16 A oferece proteção contra curtos-circuitos, enquanto a caixa plástica IP65 compacta protege os componentes em diferentes ambientes de instalação.\n\nATENÇÃO: os botões da porta e do quadro de comando são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos é realizado por flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual.\n\nInstalação e uso técnico: o equipamento deve ser instalado e manuseado por profissionais qualificados ou com conhecimento técnico adequado. Isso garante o pleno desempenho do produto e a segurança da aplicação. O suporte pós-venda está disponível para orientações comerciais e de uso geral; dúvidas técnicas específicas sobre instalação devem ser direcionadas a profissionais habilitados da área elétrica.\n\nAplicações: controle de velocidade em máquinas industriais, bombas d'água, ventiladores, exaustores, transportadores, esteiras, misturadores e agitadores. Este painel é uma solução robusta, confiável e de fácil operação para diferentes processos industriais.",
    'features' => json_encode([
        'Entrada monofásica 220 V',
        'Saída trifásica 220 V',
        'Potência: atende motores trifásicos de 0,5 CV até 3 CV',
        'Conexão de entrada: fase + fase ou fase + neutro em 220 V',
        'Conexão de saída para motor: U, V, W e terra',
        'Potenciômetro de 10 kΩ incorporado à IHM',
        'Botão duplo liga/desliga',
        'Parada de emergência tipo cogumelo com trava',
        'IHM remota para monitoramento e ajuste de parâmetros',
        'Disjuntor bipolar de 16 A',
        'Caixa plástica IP65 de 210 × 280 × 130 mm',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'benefits' => json_encode([
        'Controle preciso da velocidade do motor',
        'Operação simples por IHM remota e potenciômetro',
        'Proteção contra curtos-circuitos',
        'Parada de emergência acessível na porta',
        'Caixa IP65 resistente para diferentes ambientes',
        'Compatibilidade com motores de 0,5 CV a 3 CV',
        'Aplicação versátil em máquinas, bombas e ventilação',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'components' => json_encode([
        'Caixa plástica IP65 de 210 × 280 × 130 mm',
        'Inversor de frequência de 3 CV',
        'IHM remota',
        'Potenciômetro de 10 kΩ',
        'Botão duplo liga/desliga',
        'Botão de emergência tipo cogumelo com trava',
        'Disjuntor bipolar de 16 A',
        'Prensa-cabos para entrada e saída',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'voltages' => 'Entrada 220 V monofásica / saída 220 V trifásica',
    'power_range' => 'De 0,5 CV até 3 CV',
    'protection_rating' => 'IP65',
    'featured_image' => '/images/painel-inversor-3cv-220v-mono-principal.png',
    'gallery_images' => json_encode([
        '/images/painel-inversor-3cv-220v-mono-interior.png',
        '/images/painel-inversor-3cv-220v-mono-perspectiva.png',
        '/images/painel-inversor-3cv-220v-mono-conexoes.png',
    ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    'video_url' => '/videos/inversor2.mp4',
    'video_urls' => json_encode(['/videos/inversor2.mp4'], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    'category_name' => 'Painel com Inversor de Frequência',
    'reference_code' => 'PAINEL_INVERSOR_3CV_MONO',
    'brand' => 'Painel de Comando',
    'model' => 'Painel com Inversor',
    'price_cents' => 209475,
    'installments' => 3,
    'stock_status' => 'in_stock',
    'stock_quantity' => 1,
    'lead_time' => 'Disponível em 3 dias úteis',
    'sales_channel' => 'both',
    'warranty_days' => 365,
    'featured' => 0,
    'status' => 'published',
    'seo_title' => 'Painel com Inversor 3CV 220V Mono | Painel de Comando',
    'seo_description' => 'Painel com inversor de 3 CV, entrada monofásica 220 V, saída trifásica, IHM remota, proteção IP65 e parada de emergência.',
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

fwrite(STDOUT, "Produto Inversor 3 CV 220 V Mono atualizado.\n");
