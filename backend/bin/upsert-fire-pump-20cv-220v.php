<?php
declare(strict_types=1);

$slug = 'painel-estrela-triangulo-bomba-incendio-20cv-220v';
$product = [
    'name' => 'Painel Estrela Triângulo para Bomba de Incêndio 20CV 220V',
    'slug' => $slug,
    'summary' => 'Painel estrela-triângulo robusto para acionamento manual ou automático de bombas de incêndio trifásicas de até 20 CV em 220 V.',
    'description' => "O Painel Estrela Triângulo para Bomba de Incêndio 20CV 220V foi desenvolvido para proporcionar partida eficiente, segurança e durabilidade em aplicações industriais e sistemas de combate a incêndio. A partida estrela-triângulo reduz o impacto da corrente de partida na rede elétrica e contribui para a proteção do motor. O comando possui seletor de três posições — manual, desligado e automático —, sinalização luminosa de comando ligado e sobrecarga, além de parada de emergência. É indicado para bombas de incêndio e aplicações exigentes em aeroportos, estádios, eventos e portos.\n\nOs botões da porta e do quadro de comando são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos é realizado por flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual.\n\nA instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado. Dúvidas técnicas específicas de instalação devem ser direcionadas a um profissional habilitado da área elétrica.",
    'features' => json_encode([
        'Número de fases: Trifásico',
        'Ligação de motores: Trifásica',
        'Limite de potência: 20 CV',
        'Tensão: 220 V',
        'Frequência: 60 Hz',
        'Variação de corrente térmica: 12,5–18 A',
        'Grau de proteção: IP54',
        'Dimensões do painel: 40 × 30 × 20 cm',
        'Seletor de 3 posições: Manual / Desligado / Automático',
        'Parada de emergência: Sim',
        'LED vermelho: Comando ligado',
        'LED amarelo: Sobrecarga',
    ], JSON_UNESCAPED_UNICODE),
    'benefits' => json_encode([
        'Eficiência e segurança: a partida estrela-triângulo reduz o impacto na rede elétrica',
        'Fácil manutenção: componentes numerados e organizados',
        'Alta durabilidade com componentes de marcas reconhecidas',
        'Instalação simplificada com conexões facilitadas por bornes',
        'Possibilidade de personalização e suporte técnico disponível',
    ], JSON_UNESCAPED_UNICODE),
    'components' => json_encode([
        'Disjuntor termomagnético',
        'Barramento',
        'Relé de falta de fase',
        'Relé estrela-triângulo',
        'Contatores de potência',
        'Relé térmico de sobrecarga',
        'Canaleta',
        'Trilho DIN',
        'Sinaleiros luminosos',
        'Seletor de três posições',
        'Botão de emergência',
        'Componentes de marcas industriais reconhecidas, sujeitos à disponibilidade em estoque',
    ], JSON_UNESCAPED_UNICODE),
    'voltages' => '220 V trifásico',
    'power_range' => 'Até 20 CV',
    'protection_rating' => 'IP54',
    'featured_image' => '/images/painel-bomba-incendio-10cv-220v-vermelho.png',
    'gallery_images' => '[]',
    'video_url' => null,
    'video_urls' => '[]',
    'category_name' => 'Bomba de Incêndio',
    'reference_code' => 'PAINEL-20CV-220V-CAIXA-VERMELHA',
    'brand' => 'Painel de Comando',
    'model' => 'Painel Estrela Triângulo para Bomba de Incêndio 20CV 220V',
    'price_cents' => 271000,
    'installments' => 3,
    'stock_status' => 'on_demand',
    'stock_quantity' => 0,
    'lead_time' => 'Disponível em 3 dias úteis',
    'sales_channel' => 'both',
    'warranty_days' => 365,
    'sort_order' => 23,
    'featured' => 1,
    'status' => 'published',
    'seo_title' => 'Painel para Bomba de Incêndio 20CV 220V | Painel de Comando',
    'seo_description' => 'Painel estrela-triângulo 20 CV 220 V para bomba de incêndio, com proteção IP54, comando manual e automático e parada de emergência.',
];

$columns = array_keys($product);
$assignments = array_map(static fn(string $column): string => "{$column}=excluded.{$column}", $columns);
$sql = sprintf(
    'INSERT INTO products (%s,published_at) VALUES (%s,CURRENT_TIMESTAMP) ON CONFLICT(slug) DO UPDATE SET %s,published_at=CURRENT_TIMESTAMP,updated_at=CURRENT_TIMESTAMP,deleted_at=NULL',
    implode(',', $columns),
    implode(',', array_map(static fn(string $column): string => ':' . $column, $columns)),
    implode(',', $assignments),
);
$pdo->prepare($sql)->execute($product);
