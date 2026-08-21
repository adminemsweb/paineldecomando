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
    static fn(array $item): bool => ($item['slug'] ?? '') === 'painel-estrela-triangulo-15cv-380v'
))[0] ?? null;
if (!is_array($product)) throw new RuntimeException('Produto 15CV 380V não encontrado.');

$product['summary'] = 'Partidas seguras e eficientes para motores trifásicos, com proteção, desempenho e economia de energia.';
$product['description'] = "Todos os produtos Painel de Comando são projetados por engenheiros especializados e fabricados com responsabilidade técnica certificada, seguindo rigorosos padrões de qualidade e testes funcionais a 100% antes do envio. Cada equipamento é desenvolvido para garantir segurança, desempenho e durabilidade superiores, refletindo o compromisso da nossa marca com a confiabilidade industrial e a precisão técnica.\n\nO Painel de Comando Estrela-Triângulo 15CV 380V é a solução ideal para partidas seguras e eficientes de motores trifásicos, oferecendo proteção, desempenho e economia de energia. Com o sistema estrela-triângulo, a corrente de partida é reduzida em até 70%, garantindo partidas suaves e maior vida útil ao motor.\n\nO modelo ECO combina robustez, funcionalidade e excelente custo-benefício, mantendo o padrão de engenharia e nosso padrão de qualidade reconhecido no mercado industrial.";
$product['features'] = [
    'Tensão: 380V Trifásico',
    'Corrente: 40A',
    'Potência do motor: até 15CV',
    'Sistema: Estrela-Triângulo',
    'Acionamento: Manual e Automático',
    'Construção: Caixa metálica reforçada com pintura eletrostática',
    'Montagem: Trilho DIN e canaletas industriais de alta qualidade',
];
$product['components'] = [
    'Caixa de comando metálica robusta',
    'Contatores de potência: 18A + 12A (bobina 220V)',
    'Relé de sobrecarga: 10–15A',
    'Mini disjuntor tripolar: 40A',
    'Relé temporizador para controle automático da transição estrela-triângulo',
    'Canaletas, trilho DIN e bornes industriais de alta durabilidade',
];
$product['benefits'] = [
    'Reduz a corrente de partida e protege o motor',
    'Evita sobrecarga e curtos-circuitos',
    'Aumenta a vida útil do sistema elétrico',
    'Instalação prática e manutenção simplificada',
    'Equipamento técnico, funcional e com excelente custo-benefício',
];
$product['brand'] = 'Painel de Comando';
$product['reference_code'] = 'PAINEL-E.T-15CV+380-MAN-AUT.ECO';
$product['model'] = 'Painel Estrela Triângulo';
$product['lead_time'] = 'Disponível em 3 dias úteis';
$product['warranty_days'] = 365;
$product['voltages'] = '380V Trifásico';
$product['power_range'] = 'Até 15CV';
$product['seo_title'] = 'Painel Estrela Triângulo 15CV 380V Man/Aut. Eco | Painel de Comando';
$product['seo_description'] = 'Painel Estrela-Triângulo Painel de Comando 15CV 380V com acionamento manual e automático.';

$repository->update((int)$product['id'], $product);
fwrite(STDOUT, "Conteúdo Painel de Comando 15CV 380V atualizado.\n");
