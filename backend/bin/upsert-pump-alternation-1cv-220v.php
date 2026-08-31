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
$slug = 'painel-condominio-revezamento-bombas-1cv-220v-trifasico';
$product = [
    'name' => 'Painel para Condomínio Revezamento de Bombas 1CV 220V Trifásico',
    'slug' => $slug,
    'summary' => 'Painel para alternância automática de duas bombas de 1 CV em 220 V trifásico, com operação manual ou automática e proteção individual dos motores.',
    'description' => "O Painel para Condomínio Revezamento de Bombas 1CV 220V Trifásico é a solução técnica para o gerenciamento de recalque em edifícios que operam com duas motobombas de baixa potência. O quadro automatiza a alternância entre os motores, distribuindo o tempo de funcionamento de forma equilibrada e ajudando a preservar rolamentos e selos mecânicos.\n\nA lógica de controle mantém o abastecimento hídrico contínuo e permite que a bomba reserva seja acionada quando a principal apresentar falha elétrica ou mecânica. O monitor de falta de fase e os relés térmicos individuais protegem os motores contra condições anormais da rede e sobrecargas.\n\nNa porta do gabinete, sinaleiros identificam comando ligado, funcionamento de cada motor e falha térmica. As chaves seletoras permitem escolher a operação manual, desligada ou automática e selecionar o motor prioritário. O gabinete metálico reforçado recebe pintura eletrostática e vedação de borracha.\n\nIndicado para condomínios e edifícios com sistemas de recalque, este painel oferece segurança operacional, alternância confiável e maior disponibilidade do abastecimento. A instalação e o manuseio devem ser realizados por profissional habilitado, conforme o projeto elétrico e as normas aplicáveis.",
    'features' => json_encode([
        'Produto: Painel de Comando para Revezamento de Bombas',
        'Potência suportada: 1 CV (0,75 kW) por motor',
        'Tensão de operação: 220 V trifásico',
        'Revezamento automático por ciclo de acionamento ou tempo',
        'Contatores industriais e relés de sobrecarga de alta precisão',
        'Proteção contra falta de fase e proteção térmica individualizada',
        'Chave seletora Manual / Desligado / Automático',
        'Seleção de prioridade entre os motores',
        'Indicadores de funcionamento e alerta de falha térmica',
        'Gabinete metálico com pintura eletrostática e vedação de borracha',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'benefits' => json_encode([
        'Abastecimento contínuo com acionamento da bomba reserva em situações de falha',
        'Desgaste equilibrado dos dois conjuntos motobomba',
        'Redução do risco de travamento causado por longos períodos sem uso',
        'Proteção dos motores contra falta de fase, sobrecarga e oscilações da rede',
        'Operação manual ou automática para maior flexibilidade',
        'Sinalização frontal que facilita o acompanhamento do sistema',
        'Construção robusta para aplicações prediais e condominiais',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'components' => json_encode([
        'Disjuntor tripolar',
        'Monitor de falta de fase',
        'Dois contatores industriais',
        'Dois relés térmicos individuais',
        'Bornes de ligação identificados',
        'Sinaleiros de comando, funcionamento e sobrecarga',
        'Chaves seletoras de modo e prioridade',
        'Botão para teste da boia',
        'Gabinete metálico com placa de montagem e canaletas',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'voltages' => '220 V trifásico',
    'power_range' => '1 CV (0,75 kW) por motor',
    'protection_rating' => 'Gabinete metálico com vedação de borracha',
    'featured_image' => '/images/painel-revezamento-bombas-1cv-220v-perspectiva.png',
    'gallery_images' => json_encode([
        '/images/painel-revezamento-bombas-1cv-220v-frontal.png',
        '/images/painel-revezamento-bombas-1cv-220v-aberto.png',
        '/images/painel-revezamento-bombas-1cv-220v-componentes.png',
    ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    'video_url' => '/videos/revezamento.mp4',
    'video_urls' => json_encode(['/videos/revezamento.mp4'], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    'category_name' => 'Revezamento de Bombas',
    'reference_code' => 'PAINEL-COND-1CV-220V-TRI',
    'brand' => 'Painel de Comando',
    'model' => 'Painel para Condomínio Revezamento de Bombas 1CV',
    'price_cents' => 150800,
    'installments' => 3,
    'stock_status' => 'on_demand',
    'stock_quantity' => 0,
    'lead_time' => 'Disponível em 3 dias úteis',
    'sales_channel' => 'both',
    'warranty_days' => 365,
    'featured' => 0,
    'status' => 'published',
    'seo_title' => 'Painel Revezamento de Bombas 1CV 220V Trifásico',
    'seo_description' => 'Painel para revezamento automático de duas bombas de 1 CV em 220 V trifásico, com proteção térmica, falta de fase e operação manual ou automática.',
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

fwrite(STDOUT, "Produto de revezamento de bombas 1 CV 220 V atualizado.\n");
