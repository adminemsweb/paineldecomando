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
$product = array_values(array_filter(
    $repository->adminAll(),
    static fn(array $item): bool => ($item['slug'] ?? '') === 'painel-estrela-triangulo-10cv-220v'
))[0] ?? null;
if (!is_array($product)) throw new RuntimeException('Produto 10CV 220V não encontrado.');

$product['summary'] = 'Partidas seguras, eficientes e econômicas para motores trifásicos de até 10CV.';
$product['description'] = "Todos os produtos Painel de Comando são projetados por engenheiros especializados e fabricados com responsabilidade técnica certificada, seguindo rigorosos padrões de qualidade e testes funcionais a 100% antes do envio. Cada equipamento é desenvolvido para garantir segurança, desempenho e durabilidade superiores, refletindo o compromisso da nossa marca com a confiabilidade industrial e a precisão técnica.\n\nO Painel de Comando Estrela-Triângulo 10CV 220V é a escolha perfeita para quem busca partidas seguras, eficientes e econômicas em motores trifásicos. Seu sistema estrela-triângulo reduz drasticamente a corrente de partida, protegendo o motor contra picos de energia, sobrecargas e curtos-circuitos.\n\nCom projeto compacto e robusto, o modelo ECO entrega funcionalidade, segurança e excelente custo-benefício, mantendo o padrão de engenharia e confiabilidade da nossa marca.";
$product['features'] = [
    'Tensão: 220V Trifásico',
    'Corrente: 40A',
    'Potência do motor: até 10CV',
    'Sistema: Estrela-Triângulo',
    'Acionamento: Manual e Automático',
    'Construção: Caixa metálica reforçada com pintura eletrostática',
    'Montagem: Trilho DIN e canaletas industriais de alta qualidade',
];
$product['components'] = [
    'Caixa de comando metálica robusta',
    'Contatores de potência: 18A + 12A (220V)',
    'Relé de sobrecarga: 11–17A',
    'Mini disjuntor tripolar: 40A',
    'Relé temporizador para controle preciso da transição estrela-triângulo',
    'Canaletas, trilho DIN e bornes industriais de alta durabilidade',
];
$product['benefits'] = [
    'Reduz a corrente de partida e o desgaste do motor',
    'Prolonga a vida útil do equipamento elétrico',
    'Protege contra curtos e sobrecargas',
    'Instalação rápida e manutenção facilitada',
    'Equipamento compacto, funcional e com excelente custo-benefício',
];
$product['brand'] = 'Painel de Comando';
$product['reference_code'] = 'PAINEL-E.T-10CV+MAN-AUT.ECO';
$product['model'] = 'Painel Estrela Triângulo';
$product['lead_time'] = 'Disponível em 3 dias úteis';
$product['warranty_days'] = 365;
$product['voltages'] = '220V Trifásico';
$product['power_range'] = 'Até 10CV';
$product['price_cents'] = 115400;
$product['installments'] = 3;
$product['seo_title'] = 'Painel Estrela Triângulo 10CV 220V Man/Aut. Eco | Painel de Comando';
$product['seo_description'] = 'Painel Estrela-Triângulo Painel de Comando 10CV 220V com acionamento manual e automático.';

$repository->update((int)$product['id'], $product);
fwrite(STDOUT, "Conteúdo Painel de Comando 10CV 220V atualizado.\n");
