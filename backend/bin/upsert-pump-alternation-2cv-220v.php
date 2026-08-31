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
$slug = 'painel-condominio-revezamento-bombas-2cv-220v-trifasico';
$product = [
    'name' => 'Painel para Condomínio Revezamento de Bombas 2CV 220V Trifásico',
    'slug' => $slug,
    'summary' => 'Painel para alternância automática de duas bombas de 2 CV em 220 V trifásico, com operação manual ou automática e proteção individual dos motores.',
    'description' => "O Painel para Condomínio Revezamento de Bombas 2CV 220V Trifásico foi desenvolvido para otimizar sistemas de recalque em edifícios e condomínios. O equipamento automatiza a alternância entre duas motobombas, distribuindo o trabalho de forma equilibrada e mantendo a bomba reserva disponível.\n\nA lógica de controle permite alternância automática a cada ciclo ou por tempo. Caso a bomba em operação apresente uma anomalia, a bomba secundária pode assumir o funcionamento, contribuindo para a continuidade do abastecimento. O monitor de falta de fase e os relés térmicos individuais protegem os motores contra instabilidades da rede e sobrecargas.\n\nNa porta do gabinete, sinaleiros de alta visibilidade indicam o estado do comando, o funcionamento de cada motor e alertas de falha. As chaves seletoras oferecem os modos Manual, Desligado e Automático, além da seleção do motor prioritário. O gabinete metálico possui pintura eletrostática e proteção contra umidade.\n\nO revezamento reduz o funcionamento contínuo de uma única bomba, equilibrando o desgaste de rolamentos e selos mecânicos e facilitando a manutenção preventiva do conjunto. A instalação e o manuseio devem ser realizados por profissional habilitado, conforme o projeto elétrico e as normas aplicáveis.",
    'features' => json_encode([
        'Produto: Painel de Comando para Revezamento de Bombas',
        'Potência suportada: 2 CV (1,5 kW) por motor',
        'Tensão de operação: 220 V trifásico',
        'Alternância automática a cada ciclo ou por tempo',
        'Contatores industriais e relés térmicos de alta sensibilidade',
        'Monitoramento contra falta de fase e sobrecarga individual',
        'Seletora Manual / Desligado / Automático',
        'Seleção de prioridade entre os motores',
        'Sinalização frontal de funcionamento e falha',
        'Gabinete metálico com pintura eletrostática e proteção contra umidade',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'benefits' => json_encode([
        'Continuidade operacional com uma bomba de reserva disponível',
        'Distribuição equilibrada do trabalho entre os motores',
        'Menor desgaste de selos mecânicos e rolamentos',
        'Proteção contra falta de fase, sobrecarga e instabilidades da rede',
        'Operação manual ou automática para maior flexibilidade',
        'Sinalização frontal que facilita o acompanhamento do sistema',
        'Automação robusta para casas de máquinas prediais',
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
    'power_range' => '2 CV (1,5 kW) por motor',
    'protection_rating' => 'Gabinete metálico com proteção contra umidade',
    'featured_image' => '/images/painel-revezamento-bombas-1cv-220v-perspectiva.png',
    'gallery_images' => json_encode([
        '/images/painel-revezamento-bombas-1cv-220v-frontal.png',
        '/images/painel-revezamento-bombas-1cv-220v-aberto.png',
        '/images/painel-revezamento-bombas-1cv-220v-componentes.png',
    ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    'video_url' => '/videos/revezamento.mp4',
    'video_urls' => json_encode(['/videos/revezamento.mp4'], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    'category_name' => 'Revezamento de Bombas',
    'reference_code' => 'PAINEL-COND-2CV-220V-TRI',
    'brand' => 'Painel de Comando',
    'model' => 'Painel para Condomínio Revezamento de Bombas 2CV',
    'price_cents' => 150800,
    'installments' => 3,
    'stock_status' => 'on_demand',
    'stock_quantity' => 0,
    'lead_time' => 'Disponível em 3 dias úteis',
    'sales_channel' => 'both',
    'warranty_days' => 365,
    'featured' => 0,
    'status' => 'published',
    'seo_title' => 'Painel Revezamento de Bombas 2CV 220V Trifásico',
    'seo_description' => 'Painel para revezamento automático de duas bombas de 2 CV em 220 V trifásico, com proteção individual e operação manual ou automática.',
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

fwrite(STDOUT, "Produto de revezamento de bombas 2 CV 220 V atualizado.\n");
