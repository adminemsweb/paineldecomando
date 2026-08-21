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
        'painel-estrela-triangulo-7-5cv-380v',
        'painel-estrela-triangulo-20cv-380v',
    ], true)
))[0] ?? null;
if (!is_array($product)) throw new RuntimeException('Produto provisório para 7,5CV 380V não encontrado.');

$product['name'] = 'Painel Estrela Triângulo 7,5CV 380V Man/Aut. Eco';
$product['slug'] = 'painel-estrela-triangulo-7-5cv-380v';
$product['summary'] = 'Partida segura e eficiente para motores trifásicos de até 7,5CV.';
$product['description'] = "Todos os produtos Painel de Comando são projetados por engenheiros especializados e fabricados com responsabilidade técnica certificada, seguindo rigorosos padrões de qualidade e testes funcionais a 100% antes do envio. Cada equipamento é desenvolvido para garantir segurança, desempenho e durabilidade superiores, refletindo nosso compromisso com a confiabilidade industrial e a precisão técnica.\n\nO Painel Estrela-Triângulo 7,5CV 380V é a solução ideal para partida segura e eficiente de motores trifásicos. Seu sistema estrela-triângulo proporciona redução significativa da corrente de partida, aumentando a vida útil do motor e garantindo proteção contra sobrecarga e curtos-circuitos.\n\nCom design compacto e funcional, o modelo ECO combina simplicidade, eficiência e excelente custo-benefício, mantendo nosso padrão técnico e de qualidade.";
$product['features'] = [
    'Tensão: 380V Trifásico',
    'Corrente: 25A',
    'Potência do motor: até 7,5CV',
    'Sistema de partida: Estrela-Triângulo, com redução da corrente de partida',
    'Acionamento: Manual e Automático',
    'Construção: Caixa metálica reforçada com pintura eletrostática',
    'Montagem: Trilho DIN e canaletas industriais de alta qualidade',
];
$product['components'] = [
    'Caixa de comando metálica robusta',
    'Contatores de potência: 9A + 9A (220V)',
    'Relé de sobrecarga: 7–10A',
    'Mini disjuntor tripolar: 25A',
    'Relé temporizador para controle automático da transição estrela-triângulo',
    'Canaletas, bornes e trilho DIN de alta qualidade',
];
$product['benefits'] = [
    'Reduz a corrente de partida do motor',
    'Protege contra sobrecarga e curtos-circuitos',
    'Aumenta a durabilidade do sistema elétrico',
    'Instalação prática e manutenção simplificada',
    'Equipamento funcional, seguro e econômico',
];
$product['brand'] = 'Painel de Comando';
$product['reference_code'] = 'PAINEL-E.T-7,5CV+380-MAN-AUT.ECO';
$product['model'] = 'Painel Estrela Triângulo';
$product['lead_time'] = 'Disponível em 3 dias úteis';
$product['warranty_days'] = 365;
$product['voltages'] = '380V Trifásico';
$product['power_range'] = 'Até 7,5CV';
$product['price_cents'] = 104800;
$product['installments'] = 3;
$product['seo_title'] = 'Painel Estrela Triângulo 7,5CV 380V Man/Aut. Eco | Painel de Comando';
$product['seo_description'] = 'Painel Estrela-Triângulo 7,5CV 380V com acionamento manual e automático.';

$repository->update((int)$product['id'], $product);
fwrite(STDOUT, "Conteúdo 7,5CV 380V atualizado.\n");
