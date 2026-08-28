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
    static fn(array $item): bool => ($item['slug'] ?? '') === 'painel-estrela-triangulo'
))[0] ?? null;
if (!is_array($product)) throw new RuntimeException('Produto principal não encontrado.');

$product['summary'] = 'Partidas seguras, suaves e eficientes para motores trifásicos, com acionamento manual e automático.';
$product['description'] = "Todos os produtos Painel de Comando são projetados por engenheiros especializados e fabricados com responsabilidade técnica certificada, seguindo rigorosos padrões de qualidade e testes funcionais a 100% antes do envio. Cada equipamento é desenvolvido para garantir segurança, desempenho e durabilidade superiores, reforçando o compromisso da nossa marca com a confiabilidade industrial e a precisão técnica.\n\nO Painel de Comando Estrela-Triângulo 15CV 220V foi projetado para oferecer partidas seguras, suaves e eficientes de motores trifásicos, com o melhor equilíbrio entre tecnologia, praticidade e economia. Seu sistema estrela-triângulo reduz significativamente a corrente de partida, evitando picos de energia e protegendo o motor contra sobrecargas e curtos-circuitos.\n\nCompacto e funcional, o modelo ECO é a opção ideal para quem busca eficiência, segurança e excelente custo-benefício com a garantia da nossa engenharia.";
$product['features'] = [
    'Tensão: 220V Trifásico',
    'Corrente: 40A',
    'Potência do motor: até 15CV',
    'Sistema: Estrela-Triângulo',
    'Acionamento: Manual e Automático',
    'Construção: Caixa metálica reforçada com pintura eletrostática',
    'Montagem: Trilho DIN e canaletas industriais de alta qualidade',
];
$product['components'] = [
    'Caixa de comando metálica robusta',
    'Contatores de potência: 25A + 18A (220V)',
    'Relé de sobrecarga: 22–32A',
    'Mini disjuntor tripolar: 63A',
    'Relé temporizador para controle preciso da transição estrela-triângulo',
    'Canaletas, trilho DIN e bornes industriais de alta durabilidade',
];
$product['benefits'] = [
    'Reduz a corrente de partida e o estresse do motor',
    'Prolonga a vida útil do sistema elétrico',
    'Protege contra curtos e sobrecargas',
    'Instalação prática e manutenção simplificada',
    'Equipamento funcional, confiável e com excelente custo-benefício',
];
$product['brand'] = 'Painel de Comando';
$product['reference_code'] = 'PAINEL-E.T-15CV+MAN-AUT.ECO';
$product['model'] = 'Painel Estrela Triângulo';
$product['lead_time'] = 'Disponível em 3 dias úteis';
$product['warranty_days'] = 365;
$product['voltages'] = '220V Trifásico';
$product['power_range'] = 'Até 15CV';
$product['seo_title'] = 'Painel Estrela Triângulo 15CV 220V Man/Aut. Eco | Painel de Comando';
$product['seo_description'] = 'Painel Estrela-Triângulo Painel de Comando 15CV 220V com acionamento manual e automático.';

$repository->update((int)$product['id'], $product);
fwrite(STDOUT, "Conteúdo Painel de Comando atualizado.\n");
