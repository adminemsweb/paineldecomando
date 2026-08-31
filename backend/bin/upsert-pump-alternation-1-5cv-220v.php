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
$slug = 'painel-condominio-revezamento-bombas-1-5cv-220v-trifasico';
$product = [
    'name' => 'Painel para Condomínio Revezamento de Bombas 1,5CV 220V Trifásico',
    'slug' => $slug,
    'summary' => 'Painel IP55 para alternância automática de duas bombas de 1,5 CV em 220 V trifásico, com acionamento manual ou automático por boia.',
    'description' => "O Painel para Condomínio Revezamento de Bombas 1,5CV 220V Trifásico oferece controle automático e seguro para dois conjuntos motobomba. Indicado para condomínios, indústrias e clubes, o sistema alterna o acionamento dos motores para equilibrar o tempo de uso e prolongar sua vida útil.\n\nO equipamento pode operar em modo manual ou automático por sinal de boia. A alternância automática evita que uma única bomba concentre todo o trabalho e mantém a bomba reserva disponível para continuidade do bombeamento. O gabinete com grau de proteção IP55 oferece resistência adequada para diferentes ambientes de instalação.\n\nNa porta do painel, sinaleiros permitem acompanhar o comando, o funcionamento de cada motor e eventuais sobrecargas. As chaves seletoras facilitam a escolha do modo de operação e do motor prioritário.\n\nATENÇÃO: os botões da porta e do quadro de comando são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos utiliza flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual.\n\nInstalação e uso técnico: o equipamento deve ser instalado e manuseado por profissionais qualificados ou com conhecimento técnico adequado. Isso garante o pleno desempenho do produto e a segurança da aplicação. Dúvidas específicas de instalação devem ser direcionadas a um profissional habilitado da área elétrica.\n\nAs imagens são meramente ilustrativas; considere as especificações desta página. Equipamento trifásico.",
    'features' => json_encode([
        'Produto: Painel de Comando para Revezamento de Bombas',
        'Potência suportada: 1,5 CV por motor',
        'Tensão de operação: 220 V trifásico',
        'Grau de proteção: IP55',
        'Acionamento manual ou automático por boia',
        'Revezamento automático entre duas bombas',
        'Proteção térmica individual dos motores',
        'Monitoramento contra falta de fase',
        'Sinalização frontal de funcionamento e sobrecarga',
        'Seleção de prioridade entre os motores',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'benefits' => json_encode([
        'Revezamento automático que equilibra o desgaste dos motores',
        'Maior continuidade operacional com uma bomba de reserva',
        'Operação manual ou automática adaptável à instalação',
        'Proteção IP55 para diferentes ambientes de trabalho',
        'Aplicação versátil em condomínios, indústrias e clubes',
        'Sinalização frontal que facilita o acompanhamento do sistema',
        'Construção robusta e manutenção simplificada',
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
        'Gabinete metálico IP55 com placa de montagem e canaletas',
    ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    'voltages' => '220 V trifásico',
    'power_range' => '1,5 CV por motor',
    'protection_rating' => 'IP55',
    'featured_image' => '/images/painel-revezamento-bombas-1cv-220v-perspectiva.png',
    'gallery_images' => json_encode([
        '/images/painel-revezamento-bombas-1cv-220v-frontal.png',
        '/images/painel-revezamento-bombas-1cv-220v-aberto.png',
        '/images/painel-revezamento-bombas-1cv-220v-componentes.png',
    ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    'video_url' => '/videos/revezamento.mp4',
    'video_urls' => json_encode(['/videos/revezamento.mp4'], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
    'category_name' => 'Revezamento de Bombas',
    'reference_code' => 'PAINEL-COND-1,5CV-220V-TRI',
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
    'seo_title' => 'Painel Revezamento de Bombas 1,5CV 220V Trifásico',
    'seo_description' => 'Painel IP55 para revezamento automático de duas bombas de 1,5 CV em 220 V trifásico, com acionamento manual ou automático por boia.',
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

fwrite(STDOUT, "Produto de revezamento de bombas 1,5 CV 220 V atualizado.\n");
