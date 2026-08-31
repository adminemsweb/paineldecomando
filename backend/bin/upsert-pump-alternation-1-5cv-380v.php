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
$slug = 'painel-condominio-revezamento-bombas-1-5cv-380v-trifasico';
$product = [
    'name' => 'Painel para Condomínio Revezamento de Bombas 1,5CV 380V Trifásico',
    'slug' => $slug,
    'summary' => 'Painel para alternância automática de duas bombas de 1,5 CV em 380 V trifásico, com operação manual ou automática e proteção individual dos motores.',
    'description' => "O Painel para Condomínio Revezamento de Bombas 1,5CV 380V Trifásico é indicado para a gestão de sistemas de recalque e pressurização em edifícios de pequeno e médio porte. O quadro automatiza a alternância entre duas motobombas, distribuindo o esforço mecânico de forma equilibrada e ajudando a preservar o conjunto.\n\nA lógica de controle alterna as bombas a cada ciclo de acionamento e mantém a unidade reserva disponível. Caso a bomba principal apresente uma interrupção elétrica ou mecânica, o sistema permite que a outra unidade assuma a operação, contribuindo para a continuidade do abastecimento hídrico.\n\nO relé de falta de fase e as proteções térmicas individuais protegem os motores contra instabilidades da rede e sobrecargas. Na porta do gabinete, LEDs indicam o funcionamento e alertas de falha térmica. A chave seletora oferece os modos Manual, Desligado e Automático, e o gabinete metálico possui pintura eletrostática e vedação industrial.\n\nAo alternar o uso das bombas, o painel reduz o desgaste excessivo de uma única unidade e o risco de travamento da bomba que permaneceria inativa. A instalação e o manuseio devem ser realizados por profissional habilitado, conforme o projeto elétrico e as normas aplicáveis.",
    'features' => json_encode([
        'Produto: Painel de Comando para Revezamento de Bombas',
        'Potência suportada: 1,5 CV (1,1 kW) por motor',
        'Tensão de operação: 380 V trifásico',
        'Revezamento automático por ciclo de acionamento',
        'Contatores industriais e relés térmicos de alta precisão',
        'Relé de falta de fase e proteção individual contra sobrecarga',
        'Seletora Manual / Desligado / Automático',
        'Seleção de prioridade entre os motores',
        'LEDs de funcionamento e alerta de falha térmica',
        'Gabinete metálico com pintura eletrostática e vedação industrial',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'benefits' => json_encode([
        'Maior continuidade do abastecimento com uma bomba reserva disponível',
        'Distribuição equilibrada do esforço entre os motores',
        'Redução do desgaste de rolamentos e selos mecânicos',
        'Proteção contra falta de fase, sobrecarga e instabilidades da rede',
        'Operação manual ou automática para maior flexibilidade',
        'Sinalização frontal que facilita o acompanhamento do sistema',
        'Automação robusta para sistemas de recalque e pressurização',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'components' => json_encode([
        'Disjuntor tripolar',
        'Relé de falta de fase para rede 380 V trifásica',
        'Dois contatores industriais',
        'Dois relés térmicos individuais',
        'Bornes de ligação identificados',
        'Sinaleiros de comando, funcionamento e sobrecarga',
        'Chaves seletoras de modo e prioridade',
        'Botão para teste da boia',
        'Gabinete metálico com placa de montagem e canaletas',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'voltages' => '380 V trifásico',
    'power_range' => '1,5 CV (1,1 kW) por motor',
    'protection_rating' => 'Gabinete metálico com vedação industrial',
    'featured_image' => '/images/painel-revezamento-bombas-1cv-220v-perspectiva.png',
    'gallery_images' => json_encode([
        '/images/painel-revezamento-bombas-1cv-220v-frontal.png',
        '/images/painel-revezamento-bombas-1cv-220v-aberto.png',
        '/images/painel-revezamento-bombas-1cv-220v-componentes.png',
    ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    'video_url' => '/videos/revezamento.mp4',
    'video_urls' => json_encode(['/videos/revezamento.mp4'], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    'category_name' => 'Revezamento de Bombas',
    'reference_code' => 'PAINEL-COND-1,5CV-380V-TRI',
    'brand' => 'Painel de Comando',
    'model' => 'Painel para Condomínio Revezamento de Bombas 1,5CV',
    'price_cents' => 150800,
    'installments' => 3,
    'stock_status' => 'on_demand',
    'stock_quantity' => 0,
    'lead_time' => 'Disponível em 3 dias úteis',
    'sales_channel' => 'both',
    'warranty_days' => 365,
    'featured' => 0,
    'status' => 'published',
    'seo_title' => 'Painel Revezamento de Bombas 1,5CV 380V Trifásico',
    'seo_description' => 'Painel para revezamento automático de duas bombas de 1,5 CV em 380 V trifásico, com proteção individual e operação manual ou automática.',
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

fwrite(STDOUT, "Produto de revezamento de bombas 1,5 CV 380 V atualizado.\n");
