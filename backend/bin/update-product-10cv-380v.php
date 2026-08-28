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
    static fn(array $item): bool => ($item['slug'] ?? '') === 'painel-estrela-triangulo-10cv-380v'
))[0] ?? null;
if (!is_array($product)) throw new RuntimeException('Produto 10CV 380V não encontrado.');

$product['summary'] = 'Partidas seguras, eficientes e econômicas para motores trifásicos de até 10CV.';
$product['description'] = "Todos os produtos Painel de Comando são projetados por engenheiros especializados e fabricados com responsabilidade técnica certificada, passando por testes funcionais a 100% antes do envio. Cada equipamento é desenvolvido para oferecer segurança, desempenho e durabilidade superiores, refletindo o compromisso da nossa marca com a confiabilidade e precisão técnica.\n\nO Painel de Comando Estrela-Triângulo 10CV 380V é a solução ideal para partidas seguras, eficientes e econômicas de motores trifásicos. Seu sistema estrela-triângulo reduz a corrente de partida em até 70%, protegendo o motor contra sobrecarga, picos de energia e curtos-circuitos.\n\nCompacto, funcional e resistente, o modelo ECO oferece excelente custo-benefício, sem abrir mão da qualidade e confiabilidade da nossa engenharia.";
$product['features'] = [
    'Tensão: 380V Trifásico',
    'Corrente: 25A',
    'Potência do motor: até 10CV',
    'Sistema: Estrela-Triângulo',
    'Acionamento: Manual e Automático',
    'Construção: Caixa metálica reforçada com pintura eletrostática',
    'Montagem: Trilho DIN e canaletas industriais de alta qualidade',
];
$product['components'] = [
    'Caixa de comando metálica de alta resistência',
    'Contatores de potência: 9A + 9A (bobina 220V)',
    'Relé de sobrecarga: 7–10A',
    'Mini disjuntor tripolar: 25A',
    'Relé temporizador para controle da transição estrela-triângulo',
    'Canaletas, trilho DIN e bornes industriais de alta durabilidade',
];
$product['benefits'] = [
    'Reduz a corrente de partida do motor',
    'Aumenta a vida útil e eficiência do sistema elétrico',
    'Protege contra curtos e sobrecargas',
    'Fácil instalação e manutenção',
    'Equipamento compacto, funcional e de excelente custo-benefício',
];
$product['brand'] = 'Painel de Comando';
$product['reference_code'] = 'PAINEL-E.T-10CV+380-MAN-AUT.ECO';
$product['model'] = 'Painel Estrela Triângulo';
$product['lead_time'] = 'Disponível em 3 dias úteis';
$product['warranty_days'] = 365;
$product['voltages'] = '380V Trifásico';
$product['power_range'] = 'Até 10CV';
$product['seo_title'] = 'Painel Estrela Triângulo 10CV 380V Man/Aut. Eco | Painel de Comando';
$product['seo_description'] = 'Painel Estrela-Triângulo Painel de Comando 10CV 380V com acionamento manual e automático.';

$repository->update((int)$product['id'], $product);
fwrite(STDOUT, "Conteúdo Painel de Comando 10CV 380V atualizado.\n");
