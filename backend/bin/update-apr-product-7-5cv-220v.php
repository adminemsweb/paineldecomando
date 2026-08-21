<?php
declare(strict_types=1);

use App\Config\Env;
use App\Database\Connection;
use App\Repositories\ProductRepository;

define('BASE_PATH', dirname(__DIR__));
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $file = BASE_PATH . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) require $file;
});

Env::load(BASE_PATH . '/.env');
$repository = new ProductRepository(Connection::get());
$products = $repository->adminAll();
$product = array_values(array_filter(
    $products,
    static fn(array $item): bool => in_array(($item['slug'] ?? ''), [
        'painel-estrela-triangulo-7-5cv-220v',
        'painel-estrela-triangulo-20cv-220v',
    ], true)
))[0] ?? null;
if (!is_array($product)) throw new RuntimeException('Produto provisório para 7,5CV 220V não encontrado.');

$product['name'] = 'Painel Estrela Triângulo 7,5CV 220V Man/Aut. Eco';
$product['slug'] = 'painel-estrela-triangulo-7-5cv-220v';
$product['summary'] = 'Partida segura, eficiente e econômica para motores trifásicos de até 7,5CV.';
$product['description'] = "Todos os produtos Painel de Comando são projetados por engenheiros especializados e fabricados com responsabilidade técnica certificada, seguindo rigorosos padrões de qualidade e testes funcionais a 100% antes do envio. Cada equipamento é desenvolvido para garantir segurança, desempenho e durabilidade superiores, refletindo o compromisso da nossa marca com a confiabilidade industrial e a precisão técnica.\n\nO Painel de Comando Estrela-Triângulo 7,5CV 220V é a solução ideal para quem busca partida segura, eficiente e econômica de motores trifásicos. Com sistema estrela-triângulo, ele reduz significativamente a corrente de partida, prolonga a vida útil do motor e garante proteção contra sobrecarga e curtos-circuitos.\n\nCompacto, funcional e robusto, o modelo ECO combina simplicidade, segurança e excelente custo-benefício, mantendo o alto padrão técnico da nossa engenharia.";
$product['features'] = [
    'Tensão: 220V Trifásico',
    'Corrente: 32A',
    'Potência do motor: até 7,5CV',
    'Sistema: Estrela-Triângulo',
    'Acionamento: Manual e Automático',
    'Construção: Caixa metálica reforçada com pintura eletrostática',
    'Montagem: Trilho DIN e canaletas industriais de alta qualidade',
];
$product['components'] = [
    'Caixa de comando metálica robusta',
    'Contatores de potência: 12A + 9A (220V)',
    'Relé de sobrecarga: 11–17A',
    'Mini disjuntor tripolar: 32A',
    'Relé temporizador para controle da transição estrela-triângulo',
    'Canaletas, trilho DIN e bornes industriais de alta durabilidade',
];
$product['benefits'] = [
    'Reduz a corrente de partida e o estresse no motor',
    'Proporciona maior vida útil aos equipamentos',
    'Protege contra sobrecarga e curtos-circuitos',
    'Instalação simples e manutenção facilitada',
    'Equipamento confiável com excelente custo-benefício',
];
$product['brand'] = 'Painel de Comando';
$product['reference_code'] = 'PAINEL-E.T-7,5CV+MAN-AUT.ECO';
$product['model'] = 'Painel Estrela Triângulo';
$product['lead_time'] = 'Disponível em 3 dias úteis';
$product['warranty_days'] = 365;
$product['voltages'] = '220V Trifásico';
$product['power_range'] = 'Até 7,5CV';
$product['price_cents'] = 108400;
$product['installments'] = 3;
$product['seo_title'] = 'Painel Estrela Triângulo 7,5CV 220V Man/Aut. Eco | Painel de Comando';
$product['seo_description'] = 'Painel Estrela-Triângulo Painel de Comando 7,5CV 220V com acionamento manual e automático.';

$repository->update((int)$product['id'], $product);
fwrite(STDOUT, "Conteúdo Painel de Comando 7,5CV 220V atualizado.\n");
