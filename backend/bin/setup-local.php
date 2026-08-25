<?php
declare(strict_types=1);

use App\Config\Env;
use App\Database\Connection;

define('BASE_PATH', dirname(__DIR__));
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $file = BASE_PATH . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) require $file;
});

Env::load(BASE_PATH . '/.env');
$pdo = Connection::get();
$schema = file_get_contents(BASE_PATH . '/database/sqlite-schema.sql');
if (!is_string($schema)) throw new RuntimeException('Schema SQLite não encontrado.');
$pdo->exec($schema);

$columns = array_column($pdo->query('PRAGMA table_info(products)')->fetchAll(), 'name');
$productColumns = [
    'components'=>'TEXT NULL', 'gallery_images'=>'TEXT NULL', 'video_url'=>'TEXT NULL', 'video_urls'=>'TEXT NULL', 'category_name'=>'TEXT NULL',
    'reference_code'=>'TEXT NULL', 'brand'=>'TEXT NULL', 'model'=>'TEXT NULL', 'price_cents'=>'INTEGER NULL',
    'installments'=>'INTEGER NOT NULL DEFAULT 1', 'stock_status'=>"TEXT NOT NULL DEFAULT 'on_demand'",
    'stock_quantity'=>'INTEGER NOT NULL DEFAULT 0', 'lead_time'=>'TEXT NULL', 'sales_channel'=>"TEXT NOT NULL DEFAULT 'both'",
    'warranty_days'=>'INTEGER NOT NULL DEFAULT 365',
];
foreach ($productColumns as $column => $definition) {
    if (!in_array($column, $columns, true)) $pdo->exec("ALTER TABLE products ADD COLUMN {$column} {$definition}");
}

$slugMigrations = [
    'painel-estrela-triangulo-12-5cv-380v-bomba-incendio-weg-k'=>'painel-estrela-triangulo-12-5cv-380v-bomba-incendio',
    'painel-estrela-triangulo-7-5cv-380v-compressor-weg-k'=>'painel-estrela-triangulo-7-5cv-380v-compressor',
    'painel-estrela-triangulo-15cv-380v-compressor-weg-k'=>'painel-estrela-triangulo-15cv-380v-compressor-2',
    'painel-estrela-triangulo-10cv-380v-compressor-weg-k'=>'painel-estrela-triangulo-10cv-380v-compressor',
];
$migrateSlug = $pdo->prepare('UPDATE products SET slug=:new_slug,updated_at=CURRENT_TIMESTAMP WHERE slug=:old_slug AND deleted_at IS NULL AND NOT EXISTS (SELECT 1 FROM products existing WHERE existing.slug=:new_slug AND existing.deleted_at IS NULL)');
foreach ($slugMigrations as $oldSlug => $newSlug) $migrateSlug->execute(['old_slug'=>$oldSlug,'new_slug'=>$newSlug]);

$pdo->exec("INSERT OR IGNORE INTO roles (name,slug,status) VALUES ('Superadministrador','superadmin','active'),('Administrador','admin','active'),('Cliente','customer','active')");
$pdo->exec("INSERT OR IGNORE INTO categories (name,slug,status,sort_order) VALUES
    ('Painéis de partida','paineis-de-partida','published',1),
    ('Painel com Soft Starter','painel-com-soft-starter','published',2),
    ('Painel com Inversor de Frequência','painel-com-inversor-de-frequencia','published',3),
    ('Bomba de Incêndio','bomba-de-incendio','published',4),
    ('Irrigação','irrigacao','published',5),
    ('Revezamento de Bombas','revezamento-de-bombas','published',6)");
$starDeltaCategoryId = (int)$pdo->query("SELECT id FROM categories WHERE slug='paineis-de-partida' LIMIT 1")->fetchColumn();
$subcategory = $pdo->prepare('INSERT OR IGNORE INTO categories (parent_id,name,slug,status,sort_order) VALUES (:parent_id,:name,:slug,\'published\',:sort_order)');
foreach ([
    ['Painel Estrela Triângulo Econômico','painel-estrela-triangulo-economico',1],
    ['Painel Estrela Triângulo Padrão','painel-estrela-triangulo-padrao',2],
    ['Painel Estrela Triângulo Com Amperímetro','painel-estrela-triangulo-com-amperimetro',3],
] as [$categoryName, $categorySlug, $categoryOrder]) {
    $subcategory->execute(['parent_id'=>$starDeltaCategoryId,'name'=>$categoryName,'slug'=>$categorySlug,'sort_order'=>$categoryOrder]);
}

$email = strtolower(Env::get('ADMIN_EMAIL', 'admin@paineldecomando.local'));
$password = Env::get('ADMIN_PASSWORD', 'Painel@2026');
$name = Env::get('ADMIN_NAME', 'Administrador');
$find = $pdo->prepare('SELECT id FROM users WHERE email=:email LIMIT 1');
$find->execute(['email' => $email]);
$userId = $find->fetchColumn();
if (!$userId) {
    $insert = $pdo->prepare('INSERT INTO users (name,email,password_hash,status) VALUES (:name,:email,:password_hash,\'active\')');
    $insert->execute(['name'=>$name,'email'=>$email,'password_hash'=>password_hash($password, PASSWORD_DEFAULT)]);
    $userId = (int)$pdo->lastInsertId();
}
$role = (int)$pdo->query("SELECT id FROM roles WHERE slug='superadmin'")->fetchColumn();
$assign = $pdo->prepare('INSERT OR IGNORE INTO user_roles (user_id,role_id) VALUES (:user_id,:role_id)');
$assign->execute(['user_id'=>(int)$userId,'role_id'=>$role]);

$count = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
if ($count === 0) {
    $seed = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at) VALUES (:name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,:protection_rating,:image,:gallery_images,:video_url,:category_name,:reference_code,:brand,:model,:price_cents,:installments,:stock_status,:stock_quantity,:lead_time,:sales_channel,:warranty_days,:sort_order,:featured,'published',CURRENT_TIMESTAMP)");
    $seed->execute(['name'=>'Painel Estrela Triângulo 15CV 220V Man/Aut. Eco','slug'=>'painel-estrela-triangulo','summary'=>'Partida segura para motores trifásicos com acionamento manual e automático.','description'=>'Painel projetado para reduzir a corrente de partida, proteger o motor e simplificar a operação industrial.','features'=>json_encode(['Potência de até 15 CV','Tensão trifásica de 220 V','Garantia de 1 ano'], JSON_UNESCAPED_UNICODE),'benefits'=>json_encode(['Redução da corrente de partida','Proteção contra sobrecarga'], JSON_UNESCAPED_UNICODE),'components'=>json_encode(['Caixa de comando metálica robusta','Contatores de potência','Relé de sobrecarga'], JSON_UNESCAPED_UNICODE),'voltages'=>'220 V','power_range'=>'Até 15 CV','protection_rating'=>'IP54','image'=>'/images/painel-estrela-triangulo-15cv-principal.png','gallery_images'=>json_encode(['/images/painel-estrela-triangulo-15cv-fechado-logo.png','/images/painel-estrela-triangulo-15cv-aberto-amarelo.png'], JSON_UNESCAPED_UNICODE),'video_url'=>'/videos/painelvideo.mp4','category_name'=>'Painéis de partida','reference_code'=>'PAINEL-E.T-15CV+MAN-AUT.ECO','brand'=>'Painel de Comando','model'=>'Painel Estrela Triângulo','price_cents'=>124700,'installments'=>3,'stock_status'=>'in_stock','stock_quantity'=>5,'lead_time'=>'Disponível em 3 dias úteis','sales_channel'=>'both','warranty_days'=>365,'sort_order'=>1,'featured'=>1]);
    $seed->execute(['name'=>'Painel com Soft Starter','slug'=>'painel-com-soft-starter','summary'=>'Partidas e paradas suaves com proteção adequada à carga.','description'=>'Solução para controle progressivo de motores e redução de esforços mecânicos.','features'=>json_encode(['Partida progressiva','Proteção eletrônica'], JSON_UNESCAPED_UNICODE),'benefits'=>json_encode(['Menor desgaste mecânico','Operação controlada'], JSON_UNESCAPED_UNICODE),'components'=>'[]','voltages'=>'220 V / 380 V / 440 V','power_range'=>'Sob dimensionamento','protection_rating'=>'IP54','image'=>'/images/montagem-painel-industrial-v2.jpg','gallery_images'=>'[]','video_url'=>null,'category_name'=>'Acionamento','reference_code'=>'SOFT-STARTER','brand'=>'Painel de Comando','model'=>'Soft Starter','price_cents'=>null,'installments'=>1,'stock_status'=>'on_demand','stock_quantity'=>0,'lead_time'=>'Produção sob consulta','sales_channel'=>'whatsapp','warranty_days'=>365,'sort_order'=>2,'featured'=>1]);
}

$existingStar = $pdo->query("SELECT reference_code FROM products WHERE slug='painel-estrela-triangulo' LIMIT 1")->fetchColumn();
if ($existingStar === null || $existingStar === false || $existingStar === '') {
    $update = $pdo->prepare("UPDATE products SET components=:components,gallery_images=:gallery_images,video_url=:video_url,category_name=:category_name,reference_code=:reference_code,brand=:brand,model=:model,price_cents=:price_cents,installments=:installments,stock_status=:stock_status,stock_quantity=:stock_quantity,lead_time=:lead_time,sales_channel=:sales_channel,warranty_days=:warranty_days WHERE slug='painel-estrela-triangulo'");
    $update->execute(['components'=>json_encode(['Caixa de comando metálica robusta','Contatores de potência','Relé de sobrecarga'], JSON_UNESCAPED_UNICODE),'gallery_images'=>json_encode(['/images/painel-estrela-triangulo-15cv-fechado-logo.png','/images/painel-estrela-triangulo-15cv-aberto-amarelo.png'], JSON_UNESCAPED_UNICODE),'video_url'=>'/videos/painelvideo.mp4','category_name'=>'Painéis de partida','reference_code'=>'PAINEL-E.T-15CV+MAN-AUT.ECO','brand'=>'Painel de Comando','model'=>'Painel Estrela Triângulo','price_cents'=>124700,'installments'=>3,'stock_status'=>'in_stock','stock_quantity'=>5,'lead_time'=>'Disponível em 3 dias úteis','sales_channel'=>'both','warranty_days'=>365]);
}

$firePumpSlug = 'painel-estrela-triangulo-bomba-incendio-10cv-220v';
$findFirePump = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findFirePump->execute(['slug'=>$firePumpSlug]);
if (!$findFirePump->fetchColumn()) {
    $insertFirePump = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) VALUES (:name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,:protection_rating,:featured_image,:gallery_images,:video_url,:video_urls,:category_name,:reference_code,:brand,:model,:price_cents,:installments,:stock_status,:stock_quantity,:lead_time,:sales_channel,:warranty_days,:sort_order,:featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description)");
    $insertFirePump->execute([
        'name'=>'Painel Estrela Triângulo para Bomba de Incêndio 10CV 220V',
        'slug'=>$firePumpSlug,
        'summary'=>'Acionamento estrela-triângulo seguro e robusto para bombas de incêndio trifásicas de até 10 CV.',
        'description'=>'Solução robusta e eficiente para o acionamento de bombas de incêndio, com comando manual, desligado e automático. A partida estrela-triângulo reduz o impacto na rede elétrica e oferece segurança, durabilidade e facilidade de manutenção. Indicado para aplicações industriais e sistemas de combate a incêndio em aeroportos, estádios, portos e ambientes exigentes.',
        'features'=>json_encode(['Número de fases: Trifásico','Ligação de motores: Trifásico','Limite de potência: 10 CV','Tensão: 220 V','Frequência: 60 Hz','Variação de corrente térmica: 12,5–18 A','Grau de proteção: IP54','Dimensões do painel: 40 × 30 × 20 cm','Seletor de 3 posições: Manual / Desligado / Automático','Parada de emergência: Sim','LED vermelho: Comando ligado','LED amarelo: Sobrecarga'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Eficiência e segurança: partida estrela-triângulo reduz o impacto na rede elétrica','Fácil manutenção: componentes numerados e bem organizados','Alta durabilidade com componentes de marcas reconhecidas','Instalação simplificada por borne e guia de orientação','Personalização conforme a necessidade e suporte técnico disponível'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor termomagnético','Barramento','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Canaleta e trilho DIN','Sinaleiros luminosos','Botão de emergência'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'220 V trifásico',
        'power_range'=>'Até 10 CV',
        'protection_rating'=>'IP54',
        'featured_image'=>'/images/painel-bomba-incendio-10cv-220v-vermelho.png',
        'gallery_images'=>'[]',
        'video_url'=>'/videos/painelinceido2.mp4',
        'video_urls'=>json_encode(['/videos/painelinceido2.mp4'], JSON_UNESCAPED_UNICODE),
        'category_name'=>'Bomba de Incêndio',
        'reference_code'=>'PAINEL-10CV-220V-CAIXA-VERMELHA',
        'brand'=>'Painel de Comando',
        'model'=>'Painel Estrela Triângulo',
        'price_cents'=>215000,
        'installments'=>3,
        'stock_status'=>'on_demand',
        'stock_quantity'=>0,
        'lead_time'=>'Disponível em 3 dias úteis',
        'sales_channel'=>'both',
        'warranty_days'=>365,
        'sort_order'=>20,
        'featured'=>1,
        'seo_title'=>'Painel Estrela Triângulo para Bomba de Incêndio 10CV 220V',
        'seo_description'=>'Painel estrela-triângulo 10 CV 220 V para bomba de incêndio, com proteção IP54, comando manual e automático e parada de emergência.',
    ]);
}
$pdo->prepare('UPDATE products SET featured_image=:featured_image,video_url=:video_url,video_urls=:video_urls WHERE slug=:slug AND deleted_at IS NULL')->execute([
    'featured_image'=>'/images/painel-bomba-incendio-10cv-220v-vermelho.png',
    'video_url'=>'/videos/painelinceido2.mp4',
    'video_urls'=>json_encode(['/videos/painelinceido2.mp4'], JSON_UNESCAPED_UNICODE),
    'slug'=>$firePumpSlug,
]);

$firePumpStandardSlug = 'painel-estrela-triangulo-12-5cv-380v-bomba-incendio';
$findFirePumpStandard = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findFirePumpStandard->execute(['slug'=>$firePumpStandardSlug]);
if (!$findFirePumpStandard->fetchColumn()) {
    $insertFirePumpStandard = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) VALUES (:name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,:protection_rating,:featured_image,:gallery_images,:video_url,:video_urls,:category_name,:reference_code,:brand,:model,:price_cents,:installments,:stock_status,:stock_quantity,:lead_time,:sales_channel,:warranty_days,:sort_order,:featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description)");
    $insertFirePumpStandard->execute([
        'name'=>'Painel Estrela Triângulo 12,5CV 380V Bomba de Incêndio',
        'slug'=>$firePumpStandardSlug,
        'summary'=>'Acionamento estrela-triângulo confiável para bombas de incêndio trifásicas de até 12,5 CV em 380 V.',
        'description'=>'Dispositivo projetado para o acionamento de bombas de incêndio pelo método estrela-triângulo. A solução oferece partida eficiente, proteção e facilidade de manutenção, com componentes industriais reconhecidos e possibilidade de personalização conforme a aplicação.',
        'features'=>json_encode(['Tensão de operação: 380 V','Potência máxima: 12,5 CV','Frequência: 60 Hz','Grau de proteção: IP54','Dimensões do painel: 40 × 30 × 20 cm','Botão duplo liga/desliga','Indicação luminosa para comando ligado e sobrecarga','Parada de emergência'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Facilidade de manutenção com diagrama elétrico incluído','Aterramento e parada de emergência para maior segurança','Componentes de marcas industriais reconhecidas','Suporte técnico disponível para orientações de instalação','Ligação simplificada por bornes e cabos anilhados','Possibilidade de personalização conforme a necessidade'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Componentes industriais de marcas reconhecidas','Diagrama elétrico','Bornes de conexão','Cabos identificados por anilhas','Sistema de aterramento','Botão duplo liga/desliga','Sinaleiros de comando e sobrecarga','Botão de parada de emergência'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'380 V trifásico',
        'power_range'=>'Até 12,5 CV',
        'protection_rating'=>'IP54',
        'featured_image'=>'/images/painel-bomba-incendio-12-5cv-380v-frente-vermelho.png',
        'gallery_images'=>json_encode(['/images/painel-bomba-incendio-12-5cv-380v-aberto-vermelho.png'], JSON_UNESCAPED_UNICODE),
        'video_url'=>'/videos/painelincendio1.mp4',
        'video_urls'=>json_encode(['/videos/painelincendio1.mp4','/videos/painelincendioexterno.mp4'], JSON_UNESCAPED_UNICODE),
        'category_name'=>'Painel Estrela Triângulo Padrão',
        'reference_code'=>'E.T_12,5CV_380V_BOT_K',
        'brand'=>'Painel de Comando',
        'model'=>'Painel Estrela Triângulo 12,5CV',
        'price_cents'=>157400,
        'installments'=>3,
        'stock_status'=>'on_demand',
        'stock_quantity'=>0,
        'lead_time'=>'Disponível em 3 dias úteis',
        'sales_channel'=>'both',
        'warranty_days'=>365,
        'sort_order'=>21,
        'featured'=>1,
        'seo_title'=>'Painel Estrela Triângulo 12,5CV 380V para Bomba de Incêndio',
        'seo_description'=>'Painel estrela-triângulo 12,5 CV 380 V para bomba de incêndio, proteção IP54, componentes industriais e parada de emergência.',
    ]);
}
$pdo->prepare('UPDATE products SET category_name=:category_name,featured_image=:featured_image,gallery_images=:gallery_images,video_url=:video_url,video_urls=:video_urls WHERE slug=:slug AND deleted_at IS NULL')->execute([
    'category_name'=>'Painel Estrela Triângulo Padrão',
    'featured_image'=>'/images/painel-bomba-incendio-12-5cv-380v-frente-vermelho.png',
    'gallery_images'=>json_encode(['/images/painel-bomba-incendio-12-5cv-380v-aberto-vermelho.png'], JSON_UNESCAPED_UNICODE),
    'video_url'=>'/videos/painelincendio1.mp4',
    'video_urls'=>json_encode(['/videos/painelincendio1.mp4','/videos/painelincendioexterno.mp4'], JSON_UNESCAPED_UNICODE),
    'slug'=>$firePumpStandardSlug,
]);

$compressorSlug = 'painel-estrela-triangulo-7-5cv-380v-compressor';
$findCompressor = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findCompressor->execute(['slug'=>$compressorSlug]);
if (!$findCompressor->fetchColumn()) {
    $insertCompressor = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) VALUES (:name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,:protection_rating,:featured_image,:gallery_images,:video_url,:video_urls,:category_name,:reference_code,:brand,:model,:price_cents,:installments,:stock_status,:stock_quantity,:lead_time,:sales_channel,:warranty_days,:sort_order,:featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description)");
    $insertCompressor->execute([
        'name'=>'Painel Estrela Triângulo 7,5CV 380V Compressor',
        'slug'=>$compressorSlug,
        'summary'=>'Controle manual e automático eficiente e seguro para compressores e bombas trifásicos de até 7,5 CV em 380 V.',
        'description'=>'O Painel Estrela Triângulo 7,5CV 380V é ideal para o acionamento de compressores e bombas, proporcionando controle manual e automático com eficiência e segurança. É projetado para iniciar a operação por meio do sistema estrela-triângulo, garantindo um desempenho confiável e suave. Este painel é uma escolha ideal para aplicações que exigem controle preciso e confiável, oferecendo durabilidade e suporte técnico especializado para atender às necessidades específicas.',
        'features'=>json_encode(['Aplicação: compressores e bombas','Modos de operação: manual e automático','Dimensões: 40 × 30 × 20 cm','Seletora de 3 posições: manual / desligado / automático','Proteção: IP54','LED vermelho: comando ligado','LED amarelo: sobrecarga','Tensão de operação: 380 V trifásico','Potência do motor: até 7,5 CV'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Funcionamento eficiente: inicia suavemente motores e bombas, reduzindo o desgaste mecânico','Segurança melhorada: inclui parada de emergência e proteção contra sobrecarga','Facilidade de manutenção: cabos anilhados e diagrama elétrico facilitam reparos e a identificação dos componentes','Alta qualidade: utiliza componentes de marcas renomadas para assegurar durabilidade e desempenho','Suporte técnico disponível para dúvidas de instalação e personalizações'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor de proteção','Barramento','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Bornes de conexão','Cabos identificados por anilhas','Componentes Altronic, Coel e equivalentes industriais'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'380 V trifásico',
        'power_range'=>'Até 7,5 CV',
        'protection_rating'=>'IP54',
        'featured_image'=>'/images/painel-compressor-7-5cv-380v-frente.png',
        'gallery_images'=>json_encode(['/images/painel-compressor-7-5cv-380v-aberto-frontal.png','/images/painel-compressor-7-5cv-380v-aberto-perspectiva.png','/images/painel-compressor-7-5cv-380v-componentes.png','/images/painel-compressor-7-5cv-380v-aterramento.png'], JSON_UNESCAPED_UNICODE),
        'video_url'=>'/videos/pianelestrela1.mp4',
        'video_urls'=>json_encode(['/videos/pianelestrela1.mp4'], JSON_UNESCAPED_UNICODE),
        'category_name'=>'Painel Estrela Triângulo Padrão',
        'reference_code'=>'E.T_7,5CV_380V_MAN_K',
        'brand'=>'Painel de Comando',
        'model'=>'Painel Estrela Triângulo 7,5CV',
        'price_cents'=>144000,
        'installments'=>3,
        'stock_status'=>'on_demand',
        'stock_quantity'=>0,
        'lead_time'=>'Disponível em 3 dias úteis',
        'sales_channel'=>'both',
        'warranty_days'=>365,
        'sort_order'=>22,
        'featured'=>1,
        'seo_title'=>'Painel Estrela Triângulo 7,5CV 380V Compressor',
        'seo_description'=>'Painel estrela-triângulo 7,5 CV 380 V para compressores e bombas, com comando manual e automático, proteção IP54 e componentes industriais.',
    ]);
}
$pdo->prepare('UPDATE products SET name=:name,summary=:summary,description=:description,features=:features,benefits=:benefits,components=:components,voltages=:voltages,power_range=:power_range,protection_rating=:protection_rating,category_name=:category_name,reference_code=:reference_code,brand=:brand,model=:model,price_cents=:price_cents,installments=:installments,stock_status=:stock_status,stock_quantity=:stock_quantity,lead_time=:lead_time,sales_channel=:sales_channel,warranty_days=:warranty_days,featured_image=:featured_image,gallery_images=:gallery_images,video_url=:video_url,video_urls=:video_urls,seo_title=:seo_title,seo_description=:seo_description,updated_at=CURRENT_TIMESTAMP WHERE slug=:slug AND deleted_at IS NULL')->execute([
    'name'=>'Painel Estrela Triângulo 7,5CV 380V Compressor',
    'summary'=>'Controle manual e automático eficiente e seguro para compressores e bombas trifásicos de até 7,5 CV em 380 V.',
    'description'=>'O Painel Estrela Triângulo 7,5CV 380V é ideal para o acionamento de compressores e bombas, proporcionando controle manual e automático com eficiência e segurança. É projetado para iniciar a operação por meio do sistema estrela-triângulo, garantindo um desempenho confiável e suave. Este painel é uma escolha ideal para aplicações que exigem controle preciso e confiável, oferecendo durabilidade e suporte técnico especializado para atender às necessidades específicas.',
    'features'=>json_encode(['Aplicação: compressores e bombas','Modos de operação: manual e automático','Dimensões: 40 × 30 × 20 cm','Seletora de 3 posições: manual / desligado / automático','Proteção: IP54','LED vermelho: comando ligado','LED amarelo: sobrecarga','Tensão de operação: 380 V trifásico','Potência do motor: até 7,5 CV'], JSON_UNESCAPED_UNICODE),
    'benefits'=>json_encode(['Funcionamento eficiente: inicia suavemente motores e bombas, reduzindo o desgaste mecânico','Segurança melhorada: inclui parada de emergência e proteção contra sobrecarga','Facilidade de manutenção: cabos anilhados e diagrama elétrico facilitam reparos e a identificação dos componentes','Alta qualidade: utiliza componentes de marcas renomadas para assegurar durabilidade e desempenho','Suporte técnico disponível para dúvidas de instalação e personalizações'], JSON_UNESCAPED_UNICODE),
    'components'=>json_encode(['Disjuntor de proteção','Barramento','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Bornes de conexão','Cabos identificados por anilhas','Componentes Altronic, Coel e equivalentes industriais'], JSON_UNESCAPED_UNICODE),
    'voltages'=>'380 V trifásico',
    'power_range'=>'Até 7,5 CV',
    'protection_rating'=>'IP54',
    'category_name'=>'Painel Estrela Triângulo Padrão',
    'reference_code'=>'E.T_7,5CV_380V_MAN_K',
    'brand'=>'Painel de Comando',
    'model'=>'Painel Estrela Triângulo 7,5CV',
    'price_cents'=>144000,
    'installments'=>3,
    'stock_status'=>'on_demand',
    'stock_quantity'=>0,
    'lead_time'=>'Disponível em 3 dias úteis',
    'sales_channel'=>'both',
    'warranty_days'=>365,
    'featured_image'=>'/images/painel-compressor-7-5cv-380v-frente.png',
    'gallery_images'=>json_encode(['/images/painel-compressor-7-5cv-380v-aberto-frontal.png','/images/painel-compressor-7-5cv-380v-aberto-perspectiva.png','/images/painel-compressor-7-5cv-380v-componentes.png','/images/painel-compressor-7-5cv-380v-aterramento.png'], JSON_UNESCAPED_UNICODE),
    'video_url'=>'/videos/pianelestrela1.mp4',
    'video_urls'=>json_encode(['/videos/pianelestrela1.mp4'], JSON_UNESCAPED_UNICODE),
    'seo_title'=>'Painel Estrela Triângulo 7,5CV 380V Compressor',
    'seo_description'=>'Painel estrela-triângulo 7,5 CV 380 V para compressores e bombas, com comando manual e automático, proteção IP54 e componentes industriais.',
    'slug'=>$compressorSlug,
]);

$compressor15Slug = 'painel-estrela-triangulo-15cv-380v-compressor';
$findCompressor15 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findCompressor15->execute(['slug'=>$compressor15Slug]);
if (!$findCompressor15->fetchColumn()) {
    $insertCompressor15 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) VALUES (:name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,:protection_rating,:featured_image,:gallery_images,:video_url,:video_urls,:category_name,:reference_code,:brand,:model,:price_cents,:installments,:stock_status,:stock_quantity,:lead_time,:sales_channel,:warranty_days,:sort_order,:featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description)");
    $insertCompressor15->execute([
        'name'=>'Painel Estrela Triângulo 15CV 380V Compressor',
        'slug'=>$compressor15Slug,
        'summary'=>'Painel estrela-triângulo para acionamento manual e automático de compressores ou bombas trifásicas de até 15 CV em 380 V.',
        'description'=>'Painel montado para acionamento de compressores ou bombas, com modos de operação manual e automático. Ideal para aplicações que necessitam do sistema estrela-triângulo para iniciar a operação. Inclui diagrama elétrico, lista de peças para reposição, cabos anilhados, ligação por bornes e guia de orientação para ligação e fechamento de motores. Não é recomendado para partidas que precisam iniciar com carga; para aplicações específicas, consulte a engenharia.',
        'features'=>json_encode(['SKU: E.T_15CV_380V_MAN_K','Número de fases: trifásico','Ligação de motores: trifásico','Potência máxima: 15 CV','Tensão: 380 V','Frequência: 60 Hz','Variação de corrente térmica: 12,5 A – 18 A','Grau de proteção: IP54','Dimensões do painel: 40 × 30 × 20 cm','Adesivo frontal','Seletora de 3 posições: manual / desligado / automático','Parada de emergência: sim','Modo manual: liga instantaneamente a bomba','Modo desligado (0): desliga a bomba','Modo automático: aguarda o sinal dos bornes da boia ou pressostato para ligar','Para painel 380 V, o motor deve ter fechamento 660 V / 380 V em estrela / triângulo'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Diagrama elétrico e lista de peças facilitam a montagem, manutenção e reposição','Aterramento para maior segurança elétrica','Cabos numerados por anilhas facilitam a identificação dos componentes','Ligação por bornes simplifica a conexão dos cabos','Componentes de marcas reconhecidas para maior durabilidade e desempenho','Suporte técnico disponível para dúvidas sobre instalação','Guia de orientação para ligação e fechamento de motores','Montagem com padrão industrial de qualidade'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor termomagnético geral','Barramento','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Canaleta','Trilho DIN','Quadro metálico','Sinaleiro luminoso','Botão de emergência','Marcas dos componentes: Altronic, Coel, View Tech, HellermannTyton, Proauto e Scame'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'380 V trifásico',
        'power_range'=>'Até 15 CV',
        'protection_rating'=>'IP54',
        'featured_image'=>'/images/painel-compressor-7-5cv-380v-frente.png',
        'gallery_images'=>json_encode(['/images/painel-compressor-7-5cv-380v-aberto-frontal.png','/images/painel-compressor-7-5cv-380v-aberto-perspectiva.png','/images/painel-compressor-7-5cv-380v-componentes.png','/images/painel-compressor-7-5cv-380v-aterramento.png'], JSON_UNESCAPED_UNICODE),
        'video_url'=>'/videos/pianelestrela1.mp4',
        'video_urls'=>json_encode(['/videos/pianelestrela1.mp4'], JSON_UNESCAPED_UNICODE),
        'category_name'=>'Painel Estrela Triângulo Padrão',
        'reference_code'=>'E.T_15CV_380V_MAN_K',
        'brand'=>'Painel de Comando',
        'model'=>'Painel Estrela Triângulo 15CV 380V',
        'price_cents'=>156100,
        'installments'=>3,
        'stock_status'=>'on_demand',
        'stock_quantity'=>0,
        'lead_time'=>'Disponível em 3 dias úteis',
        'sales_channel'=>'both',
        'warranty_days'=>365,
        'sort_order'=>23,
        'featured'=>1,
        'seo_title'=>'Painel Estrela Triângulo 15CV 380V Compressor | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 15 CV 380 V para compressores e bombas, com operação manual e automática, proteção IP54 e parada de emergência.',
    ]);
}
$pdo->prepare('UPDATE products SET name=:name,summary=:summary,description=:description,features=:features,benefits=:benefits,components=:components,voltages=:voltages,power_range=:power_range,protection_rating=:protection_rating,category_name=:category_name,reference_code=:reference_code,brand=:brand,model=:model,price_cents=:price_cents,installments=:installments,stock_status=:stock_status,stock_quantity=:stock_quantity,lead_time=:lead_time,sales_channel=:sales_channel,warranty_days=:warranty_days,featured_image=:featured_image,gallery_images=:gallery_images,video_url=:video_url,video_urls=:video_urls,seo_title=:seo_title,seo_description=:seo_description,updated_at=CURRENT_TIMESTAMP WHERE slug=:slug AND deleted_at IS NULL')->execute([
    'name'=>'Painel Estrela Triângulo 15CV 380V Compressor',
    'summary'=>'Painel estrela-triângulo para acionamento manual e automático de compressores ou bombas trifásicas de até 15 CV em 380 V.',
    'description'=>'Painel montado para acionamento de compressores ou bombas, com modos de operação manual e automático. Ideal para aplicações que necessitam do sistema estrela-triângulo para iniciar a operação. Inclui diagrama elétrico, lista de peças para reposição, cabos anilhados, ligação por bornes e guia de orientação para ligação e fechamento de motores. Não é recomendado para partidas que precisam iniciar com carga; para aplicações específicas, consulte a engenharia.',
    'features'=>json_encode(['SKU: E.T_15CV_380V_MAN_K','Número de fases: trifásico','Ligação de motores: trifásico','Potência máxima: 15 CV','Tensão: 380 V','Frequência: 60 Hz','Variação de corrente térmica: 12,5 A – 18 A','Grau de proteção: IP54','Dimensões do painel: 40 × 30 × 20 cm','Adesivo frontal','Seletora de 3 posições: manual / desligado / automático','Parada de emergência: sim','Modo manual: liga instantaneamente a bomba','Modo desligado (0): desliga a bomba','Modo automático: aguarda o sinal dos bornes da boia ou pressostato para ligar','Para painel 380 V, o motor deve ter fechamento 660 V / 380 V em estrela / triângulo'], JSON_UNESCAPED_UNICODE),
    'benefits'=>json_encode(['Diagrama elétrico e lista de peças facilitam a montagem, manutenção e reposição','Aterramento para maior segurança elétrica','Cabos numerados por anilhas facilitam a identificação dos componentes','Ligação por bornes simplifica a conexão dos cabos','Componentes de marcas reconhecidas para maior durabilidade e desempenho','Suporte técnico disponível para dúvidas sobre instalação','Guia de orientação para ligação e fechamento de motores','Montagem com padrão industrial de qualidade'], JSON_UNESCAPED_UNICODE),
    'components'=>json_encode(['Disjuntor termomagnético geral','Barramento','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Canaleta','Trilho DIN','Quadro metálico','Sinaleiro luminoso','Botão de emergência','Marcas dos componentes: Altronic, Coel, View Tech, HellermannTyton, Proauto e Scame'], JSON_UNESCAPED_UNICODE),
    'voltages'=>'380 V trifásico',
    'power_range'=>'Até 15 CV',
    'protection_rating'=>'IP54',
    'category_name'=>'Painel Estrela Triângulo Padrão',
    'reference_code'=>'E.T_15CV_380V_MAN_K',
    'brand'=>'Painel de Comando',
    'model'=>'Painel Estrela Triângulo 15CV 380V',
    'price_cents'=>156100,
    'installments'=>3,
    'stock_status'=>'on_demand',
    'stock_quantity'=>0,
    'lead_time'=>'Disponível em 3 dias úteis',
    'sales_channel'=>'both',
    'warranty_days'=>365,
    'featured_image'=>'/images/painel-compressor-7-5cv-380v-frente.png',
    'gallery_images'=>json_encode(['/images/painel-compressor-7-5cv-380v-aberto-frontal.png','/images/painel-compressor-7-5cv-380v-aberto-perspectiva.png','/images/painel-compressor-7-5cv-380v-componentes.png','/images/painel-compressor-7-5cv-380v-aterramento.png'], JSON_UNESCAPED_UNICODE),
    'video_url'=>'/videos/pianelestrela1.mp4',
    'video_urls'=>json_encode(['/videos/pianelestrela1.mp4'], JSON_UNESCAPED_UNICODE),
    'seo_title'=>'Painel Estrela Triângulo 15CV 380V Compressor | Painel de Comando',
    'seo_description'=>'Painel estrela-triângulo 15 CV 380 V para compressores e bombas, com operação manual e automática, proteção IP54 e parada de emergência.',
    'slug'=>$compressor15Slug,
]);

$compressor15VariantSlug = 'painel-estrela-triangulo-15cv-380v-compressor-2';
$findCompressor15Variant = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findCompressor15Variant->execute(['slug'=>$compressor15VariantSlug]);
if (!$findCompressor15Variant->fetchColumn()) {
    $cloneCompressor15Variant = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,24,featured,'published',CURRENT_TIMESTAMP,:seo_title,seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $cloneCompressor15Variant->execute([
        'name'=>'Painel Estrela Triângulo 15CV 380V Compressor',
        'slug'=>$compressor15VariantSlug,
        'seo_title'=>'Painel Estrela Triângulo 15CV 380V Compressor | Painel de Comando',
        'source_slug'=>$compressor15Slug,
    ]);
}

$compressor10_380Slug = 'painel-estrela-triangulo-10cv-380v-compressor';
$findCompressor10_380 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findCompressor10_380->execute(['slug'=>$compressor10_380Slug]);
if (!$findCompressor10_380->fetchColumn()) {
    $cloneCompressor10_380 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,description,:features,benefits,components,voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,:model,:price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,25,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $cloneCompressor10_380->execute([
        'name'=>'Painel Estrela Triângulo 10CV 380V Compressor',
        'slug'=>$compressor10_380Slug,
        'summary'=>'Painel estrela-triângulo para acionamento manual e automático de compressores ou bombas trifásicas de até 10 CV em 380 V.',
        'features'=>json_encode(['SKU: E.T_10CV_380V_MAN_K','Número de fases: trifásico','Ligação de motores: trifásico','Potência máxima: 10 CV','Tensão: 380 V','Frequência: 60 Hz','Variação de corrente térmica: 12,5 A – 18 A','Grau de proteção: IP54','Dimensões do painel: 40 × 30 × 20 cm','Adesivo frontal','Seletora de 3 posições: manual / desligado / automático','Parada de emergência: sim','Modo manual: liga instantaneamente a bomba','Modo desligado (0): desliga a bomba','Modo automático: aguarda o sinal dos bornes da boia ou pressostato para ligar','Para painel 380 V, o motor deve ter fechamento 660 V / 380 V em estrela / triângulo'], JSON_UNESCAPED_UNICODE),
        'power_range'=>'Até 10 CV',
        'reference_code'=>'E.T_10CV_380V_MAN_K',
        'model'=>'Painel Estrela Triângulo 10CV 380V',
        'price_cents'=>144000,
        'seo_title'=>'Painel Estrela Triângulo 10CV 380V Compressor | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 10 CV 380 V para compressores e bombas, com operação manual e automática, proteção IP54 e parada de emergência.',
        'source_slug'=>$compressor15VariantSlug,
    ]);
}
$pdo->prepare('UPDATE products SET name=:name,summary=:summary,description=:description,features=:features,benefits=:benefits,components=:components,power_range=:power_range,reference_code=:reference_code,brand=:brand,model=:model,price_cents=:price_cents,installments=:installments,lead_time=:lead_time,sales_channel=:sales_channel,warranty_days=:warranty_days,seo_title=:seo_title,seo_description=:seo_description,updated_at=CURRENT_TIMESTAMP WHERE slug=:slug AND deleted_at IS NULL')->execute([
    'name'=>'Painel Estrela Triângulo 10CV 380V Compressor',
    'summary'=>'Solução robusta e eficiente para controle manual e automático de compressores e bombas trifásicos de até 10 CV em 380 V.',
    'description'=>'O Painel Estrela Triângulo 10CV 380V é projetado especialmente para o acionamento de compressores e bombas, com modos de operação manual e automático. Ideal para aplicações que requerem a partida estrela-triângulo, garantindo um início suave e eficiente. É uma solução robusta para ambientes industriais e comerciais, proporcionando maior segurança e facilidade de uso.',
    'features'=>json_encode(['Modos de operação: manual e automático','Número de fases: trifásico','Limite de potência: 10 CV','Tensão: 380 V','Frequência: 60 Hz','Variação de corrente térmica: 8 A – 12,5 A','Grau de proteção: IP54','Dimensões: 40 × 30 × 20 cm','Seletora de 3 posições: manual / desligado / automático','LED vermelho: comando ligado','LED amarelo: sobrecarga'], JSON_UNESCAPED_UNICODE),
    'benefits'=>json_encode(['Controle flexível: alternância fácil entre os modos manual e automático','Segurança: parada de emergência e indicadores luminosos para uma operação segura','Alta qualidade: componentes de marcas renomadas asseguram durabilidade e desempenho','Suporte técnico especializado para instalação e manutenção','Montagem personalizada conforme a necessidade específica'], JSON_UNESCAPED_UNICODE),
    'components'=>json_encode(['Disjuntor termomagnético','Barramento','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Canaleta','Trilho DIN','Sinaleiro luminoso','Botão de emergência'], JSON_UNESCAPED_UNICODE),
    'power_range'=>'Até 10 CV',
    'reference_code'=>'E.T_10CV_380V_MAN_K',
    'brand'=>'Painel de Comando',
    'model'=>'Painel Estrela Triângulo 10CV 380V',
    'price_cents'=>144000,
    'installments'=>3,
    'lead_time'=>'Disponível em 3 dias úteis',
    'sales_channel'=>'both',
    'warranty_days'=>365,
    'seo_title'=>'Painel Estrela Triângulo 10CV 380V Compressor | Painel de Comando',
    'seo_description'=>'Painel estrela-triângulo 10 CV 380 V para compressores e bombas, com operação manual e automática, proteção IP54 e corrente térmica de 8 A a 12,5 A.',
    'slug'=>$compressor10_380Slug,
]);

$pushButton15Slug = 'painel-estrela-triangulo-15cv-220v-botoeiras';
$findPushButton15 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findPushButton15->execute(['slug'=>$pushButton15Slug]);
if (!$findPushButton15->fetchColumn()) {
    $insertPushButton15 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) VALUES (:name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,:protection_rating,:featured_image,:gallery_images,:video_url,:video_urls,:category_name,:reference_code,:brand,:model,:price_cents,:installments,:stock_status,:stock_quantity,:lead_time,:sales_channel,:warranty_days,:sort_order,:featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description)");
    $insertPushButton15->execute([
        'name'=>'Painel Estrela Triângulo 15CV 220V Botoeiras',
        'slug'=>$pushButton15Slug,
        'summary'=>'Painel estrela-triângulo robusto para acionamento manual e automático de compressores, bombas e máquinas trifásicas de até 15 CV em 220 V.',
        'description'=>'O Painel Estrela Triângulo 15CV 220V com botoeiras é uma solução robusta e eficiente para o acionamento de compressores, bombas e máquinas industriais. Proporciona comando manual ou automático e partida estrela-triângulo suave, com proteção, durabilidade e facilidade de uso. É indicado para compressores, betoneiras, fornos industriais, esteiras, aeroportos, estádios, shows, portos e outras aplicações industriais. Não é recomendado para partidas que necessitem iniciar com carga; para essas aplicações, consulte a engenharia.',
        'features'=>json_encode(['Número de fases: trifásico','Ligação de motores: trifásico','Limite de potência: 15 CV','Tensão: 220 V','Frequência: 60 Hz','Contatores K1 e K2: 25 A','Contator K3: 18 A','Variação de corrente térmica: 22 A – 32 A','A corrente do relé térmico é dividida por dois contatores','Grau de proteção: IP54','Dimensões: 40 × 30 × 20 cm','Adesivo frontal','Comando por botoeiras liga e desliga','Parada de emergência: sim','LED vermelho: comando ligado','LED amarelo: sobrecarga'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Diagrama elétrico completo facilita a compreensão do circuito e a manutenção','Sistema de aterramento aumenta a segurança da instalação','Cabos de comando numerados por anilhas para fácil identificação','Componentes de marcas reconhecidas para maior qualidade e confiabilidade','Suporte técnico especializado para orientações de instalação','Guia de ligação de motores com instruções para conexões corretas','Ligação rápida por bornes para maior agilidade na montagem','Montagem profissional com acabamento e padronização industrial'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor termomagnético geral','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Canaleta','Trilho DIN','Quadro metálico','Sinaleiros luminosos','Botoeiras liga e desliga','Botão de emergência','Componentes de marcas industriais reconhecidas, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'220 V trifásico',
        'power_range'=>'Até 15 CV',
        'protection_rating'=>'IP54',
        'featured_image'=>'/images/painel-botoeiras-15cv-220v-frente.png',
        'gallery_images'=>json_encode(['/images/painel-botoeiras-15cv-220v-aberto-frontal.png','/images/painel-botoeiras-15cv-220v-aberto-perspectiva.png','/images/painel-botoeiras-15cv-220v-componentes.png'], JSON_UNESCAPED_UNICODE),
        'video_url'=>null,
        'video_urls'=>'[]',
        'category_name'=>'Painel Estrela Triângulo Padrão',
        'reference_code'=>'E.T_15CV_220V_BOT_K',
        'brand'=>'Painel de Comando',
        'model'=>'Painel Estrela Triângulo',
        'price_cents'=>176500,
        'installments'=>3,
        'stock_status'=>'on_demand',
        'stock_quantity'=>0,
        'lead_time'=>'Disponível em 3 dias úteis',
        'sales_channel'=>'both',
        'warranty_days'=>365,
        'sort_order'=>26,
        'featured'=>1,
        'seo_title'=>'Painel Estrela Triângulo 15CV 220V Botoeiras | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 15 CV 220 V com botoeiras, comando manual e automático, proteção IP54, sinaleiros e parada de emergência.',
    ]);
}

$compressor15_220Slug = 'painel-estrela-triangulo-15cv-220v-compressor';
$findCompressor15_220 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findCompressor15_220->execute(['slug'=>$compressor15_220Slug]);
if (!$findCompressor15_220->fetchColumn()) {
    $insertCompressor15_220 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) VALUES (:name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,:protection_rating,:featured_image,:gallery_images,:video_url,:video_urls,:category_name,:reference_code,:brand,:model,:price_cents,:installments,:stock_status,:stock_quantity,:lead_time,:sales_channel,:warranty_days,:sort_order,:featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description)");
    $insertCompressor15_220->execute([
        'name'=>'Painel Estrela Triângulo 15CV 220V Compressor',
        'slug'=>$compressor15_220Slug,
        'summary'=>'Painel estrela-triângulo para acionamento manual e automático de motores, bombas e compressores trifásicos de até 15 CV em 220 V.',
        'description'=>'O Painel Estrela Triângulo 15CV 220V para compressor é uma solução robusta e eficiente para acionamento de motores, bombas e compressores industriais. Projetado para partidas automáticas estrela-triângulo, reduz o pico de corrente na partida e proporciona maior proteção elétrica, durabilidade e segurança operacional. É indicado para compressores industriais, betoneiras, bombas d’água, fornos, esteiras, portos, aeroportos, estádios, canteiros de obras e eventos de grande porte. Não é recomendado para partidas que iniciam com carga; para essas aplicações, consulte a engenharia.',
        'features'=>json_encode(['Número de fases: trifásico','Ligação de motores: trifásica','Potência máxima: 15 CV','Tensão: 220 V','Frequência: 60 Hz','Faixa de corrente térmica: 12,5 A – 18 A','A corrente do relé térmico é dividida por dois devido à comutação dos contatores','Grau de proteção: IP54','Dimensões: 40 × 30 × 20 cm','Adesivo frontal incluso','Seletora de 3 posições: manual / desligado / automático','Parada de emergência: sim','LED vermelho: comando ligado','LED amarelo: sobrecarga'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Diagrama elétrico completo facilita a compreensão do circuito e a manutenção','Sistema de aterramento aumenta a segurança da instalação','Cabos de comando numerados por anilhas para fácil identificação','Componentes de marcas reconhecidas para maior qualidade e confiabilidade','Suporte técnico especializado para orientações de instalação','Guia de ligação de motores com instruções para conexões corretas','Ligação rápida por bornes para maior agilidade na montagem','Montagem profissional com acabamento e padronização industrial'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor termomagnético geral','Barramento de potência','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Canaleta de cabeamento','Trilho DIN','Sinaleiro luminoso','Seletora de três posições','Botão de emergência','Componentes de marcas industriais reconhecidas, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'220 V trifásico',
        'power_range'=>'Até 15 CV',
        'protection_rating'=>'IP54',
        'featured_image'=>'/images/painel-compressor-7-5cv-380v-frente.png',
        'gallery_images'=>json_encode(['/images/painel-compressor-7-5cv-380v-aberto-frontal.png','/images/painel-compressor-7-5cv-380v-aberto-perspectiva.png','/images/painel-compressor-7-5cv-380v-componentes.png','/images/painel-compressor-7-5cv-380v-aterramento.png'], JSON_UNESCAPED_UNICODE),
        'video_url'=>'/videos/pianelestrela1.mp4',
        'video_urls'=>json_encode(['/videos/pianelestrela1.mp4'], JSON_UNESCAPED_UNICODE),
        'category_name'=>'Painel Estrela Triângulo Padrão',
        'reference_code'=>'E.T_15CV_220V_MAN_K',
        'brand'=>'Painel de Comando',
        'model'=>'Painel Estrela Triângulo',
        'price_cents'=>165000,
        'installments'=>3,
        'stock_status'=>'on_demand',
        'stock_quantity'=>0,
        'lead_time'=>'Disponível em 3 dias úteis',
        'sales_channel'=>'both',
        'warranty_days'=>365,
        'sort_order'=>27,
        'featured'=>1,
        'seo_title'=>'Painel Estrela Triângulo 15CV 220V Compressor | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 15 CV 220 V para compressores e bombas, com operação manual e automática, proteção IP54 e parada de emergência.',
    ]);
}

$compressor12_220Slug = 'painel-estrela-triangulo-12-5cv-220v-compressor';
$findCompressor12_220 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findCompressor12_220->execute(['slug'=>$compressor12_220Slug]);
if (!$findCompressor12_220->fetchColumn()) {
    $cloneCompressor12_220 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,:description,:features,benefits,components,voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,model,:price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,28,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $cloneCompressor12_220->execute([
        'name'=>'Painel Estrela Triângulo 12,5CV 220V Compressor',
        'slug'=>$compressor12_220Slug,
        'summary'=>'Painel estrela-triângulo para acionamento manual e automático de compressores, bombas e motores trifásicos de até 12,5 CV em 220 V.',
        'description'=>'O Painel Estrela Triângulo 12,5CV 220V para compressor foi desenvolvido para oferecer eficiência, segurança e durabilidade em aplicações industriais. Projetado para partidas automáticas estrela-triângulo, reduz significativamente a corrente de partida, aumenta a vida útil do motor e melhora o desempenho operacional. É indicado para compressores industriais, bombas d’água, sistemas hidráulicos, fornos, esteiras, equipamentos elétricos, canteiros de obras, aeroportos, estádios, shows e portos. Não é recomendado para partidas com carga inicial; para essas aplicações, consulte a engenharia.',
        'features'=>json_encode(['Número de fases: trifásico','Ligação de motores: trifásica','Potência máxima: 12,5 CV','Tensão: 220 V','Frequência: 60 Hz','Faixa de corrente térmica: 12,5 A – 18 A','A corrente do relé térmico é dividida por dois devido à comutação dos contatores','Grau de proteção: IP54','Dimensões: 40 × 30 × 20 cm','Adesivo frontal incluso','Seletora de 3 posições: manual / desligado / automático','Parada de emergência: sim','LED vermelho: comando ligado','LED amarelo: sobrecarga'], JSON_UNESCAPED_UNICODE),
        'power_range'=>'Até 12,5 CV',
        'reference_code'=>'E.T_125CV_220V_MAN_K',
        'price_cents'=>159500,
        'seo_title'=>'Painel Estrela Triângulo 12,5CV 220V Compressor | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 12,5 CV 220 V para compressores e bombas, com operação manual e automática, proteção IP54 e parada de emergência.',
        'source_slug'=>$compressor15_220Slug,
    ]);
}

$compressor10_220Slug = 'painel-estrela-triangulo-10cv-220v-compressor';
$findCompressor10_220 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findCompressor10_220->execute(['slug'=>$compressor10_220Slug]);
if (!$findCompressor10_220->fetchColumn()) {
    $cloneCompressor10_220 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,:description,:features,:benefits,:components,voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,:model,:price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,29,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $cloneCompressor10_220->execute([
        'name'=>'Painel Estrela Triângulo 10CV 220V Compressor',
        'slug'=>$compressor10_220Slug,
        'summary'=>'Painel estrela-triângulo para acionamento manual e automático de compressores, bombas e motores trifásicos de até 10 CV em 220 V.',
        'description'=>'O Painel Estrela Triângulo 10CV 220V para compressor foi projetado para aplicações que exigem partida estrela-triângulo, garantindo eficiência, proteção e segurança em instalações industriais. Ideal para compressores, bombas, sistemas de recalque e motores trifásicos, proporciona partida suave, reduz picos de corrente e aumenta a durabilidade do motor. Não é recomendado para partidas sob carga; para essas aplicações, consulte a engenharia.',
        'features'=>json_encode(['Número de fases: trifásico','Ligação de motores: trifásica','Potência máxima: 10 CV','Tensão: 220 V','Frequência: 60 Hz','Faixa de corrente térmica: 12,5 A – 18 A','A corrente do relé térmico é dividida por dois devido à comutação entre contatores','Grau de proteção: IP54','Dimensões: 40 × 30 × 20 cm','Adesivo frontal incluso','Seletora de 3 posições: manual / desligado / automático','Parada de emergência: sim','LED vermelho: comando ligado','LED amarelo: sobrecarga','Modo manual: acionamento direto do compressor ou bomba','Modo desligado (0): painel desativado','Modo automático: operação por sinal de boia ou pressostato'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Reduz a corrente de partida dos motores','Prolonga a vida útil dos equipamentos','Montagem profissional, organizada e segura','Suporte técnico especializado','Confiabilidade para aplicações industriais','Diagrama elétrico facilita a compreensão e a manutenção','Sistema de aterramento reforça a segurança da instalação','Cabos identificados e numerados para facilitar a manutenção','Ligação por bornes para uma instalação prática e ágil'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor termomagnético geral','Barramento de distribuição','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Canaleta e trilho DIN','Sinaleiro luminoso','Seletora de três posições','Botão de emergência','Componentes de marcas industriais reconhecidas, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'power_range'=>'Até 10 CV',
        'reference_code'=>'E.T_10CV_220V_PAINEL_COMANDO',
        'model'=>'Painel Estrela Triângulo',
        'price_cents'=>155300,
        'seo_title'=>'Painel Estrela Triângulo 10CV 220V Compressor | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 10 CV 220 V para compressores e bombas, com operação manual e automática, proteção IP54 e parada de emergência.',
        'source_slug'=>$compressor12_220Slug,
    ]);
}

$amperimeter50Slug = 'painel-estrela-triangulo-50cv-380v-manual-automatico-com-amperimetro';
$findAmperimeter50 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findAmperimeter50->execute(['slug'=>$amperimeter50Slug]);
if (!$findAmperimeter50->fetchColumn()) {
    $insertAmperimeter50 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) VALUES (:name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,:protection_rating,:featured_image,:gallery_images,NULL,'[]',:category_name,:reference_code,:brand,:model,:price_cents,:installments,:stock_status,:stock_quantity,:lead_time,:sales_channel,:warranty_days,:sort_order,:featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description)");
    $insertAmperimeter50->execute([
        'name'=>'Painel Estrela Triângulo 50CV 380V Manual Automático',
        'slug'=>$amperimeter50Slug,
        'summary'=>'Painel estrela-triângulo de 50 CV em 380 V, com amperímetro digital e acionamento manual ou automático para motores industriais de grande porte.',
        'description'=>'O Painel Estrela Triângulo 50CV 380V é uma solução robusta para o acionamento manual e automático de motores de grande porte, ideal para aplicações industriais exigentes. O amperímetro digital permite acompanhar a corrente durante a operação, enquanto as proteções contra falta de fase, sobrecarga e a parada de emergência aumentam a segurança do sistema. Os botões da porta e do quadro são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos utiliza flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual. A instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado.',
        'features'=>json_encode(['SKU: E.T_50CV_380V_MAN','Dimensões: 60 × 50 × 20 cm','Adesivo frontal','Botão duplo','Seletora manual / desligado / automático','Parada de emergência','Amperímetro digital para monitoramento da corrente','LED vermelho: comando ligado','LED amarelo: sobrecarga','Tensão: 380 V','Potência máxima: 50 CV'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Operação manual e automática para diferentes necessidades','Monitoramento da corrente e do desempenho pelo amperímetro digital','Proteção contra falha de fase, sobrecarga e parada de emergência','Componentes industriais de alta qualidade e longa vida útil','Bornes para conexões rápidas e seguras','Montagem organizada para facilitar instalação e manutenção'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor geral','Barramento estrela-triângulo','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Amperímetro digital','Bornes para cabos de até 25 mm²','Canaleta e trilho DIN','Sinaleiros luminosos','Seletora manual / desligado / automático','Botão de emergência','Componentes Coel, Altronic e Scame, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'380 V',
        'power_range'=>'Até 50 CV',
        'protection_rating'=>'IP54',
        'featured_image'=>'/images/painel-estrela-triangulo-50cv-380v-amperimetro-frente.png',
        'gallery_images'=>json_encode(['/images/painel-compressor-7-5cv-380v-aberto-frontal.png','/images/painel-compressor-7-5cv-380v-aberto-perspectiva.png','/images/painel-compressor-7-5cv-380v-componentes.png','/images/painel-compressor-7-5cv-380v-aterramento.png'], JSON_UNESCAPED_UNICODE),
        'category_name'=>'Painel Estrela Triângulo Com Amperímetro',
        'reference_code'=>'E.T_50CV_380V_MAN',
        'brand'=>'Painel de Comando',
        'model'=>'Painel Estrela Triângulo 50CV 380V',
        'price_cents'=>319800,
        'installments'=>3,
        'stock_status'=>'in_stock',
        'stock_quantity'=>5,
        'lead_time'=>'Disponível em 3 dias úteis',
        'sales_channel'=>'both',
        'warranty_days'=>365,
        'sort_order'=>30,
        'featured'=>1,
        'seo_title'=>'Painel Estrela Triângulo 50CV 380V com Amperímetro | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 50 CV 380 V com amperímetro digital, operação manual e automática, proteção IP54 e parada de emergência.',
    ]);
}

$amperimeter25Slug = 'painel-estrela-triangulo-25cv-220v-manual-automatico-com-amperimetro';
$findAmperimeter25 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findAmperimeter25->execute(['slug'=>$amperimeter25Slug]);
if (!$findAmperimeter25->fetchColumn()) {
    $cloneAmperimeter25 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,:model,:price_cents,:installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,31,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $cloneAmperimeter25->execute([
        'name'=>'Painel Estrela Triângulo 25CV 220V Manual Automático',
        'slug'=>$amperimeter25Slug,
        'summary'=>'Painel estrela-triângulo para motores de até 25 CV em 220 V, projetado para operação manual ou automática por boia ou pressostato.',
        'description'=>'O Painel Estrela Triângulo 25CV 220V foi projetado para o controle seguro e eficiente de motores em ambientes industriais, com operação manual ou automática por sinal de boia ou pressostato. O conjunto oferece monitoramento visual do estado de operação, proteção contra falta de fase e sobrecarga, parada de emergência e bornes industriais para conexões seguras. Os botões da porta e do quadro são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos utiliza flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual. A instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado.',
        'features'=>json_encode(['SKU: E.T_25CV_220V_MAN','Dimensões: 50 × 40 × 20 cm','Adesivo frontal','Seletora manual / desligado / automático','Operação automática por boia ou pressostato','Parada de emergência','Amperímetro digital para monitoramento da corrente','LED de comando ligado e monitoramento','LED amarelo: sobrecarga','Tensão: 220 V','Potência máxima: 25 CV'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Alternância entre os modos manual e automático','Monitoramento rápido do estado de operação e da corrente','Proteção integrada com disjuntor e relés','Componentes industriais de alta durabilidade','Bornes de qualidade para conexões seguras','Montagem organizada para facilitar instalação e manutenção'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor geral','Barramento estrela-triângulo','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Amperímetro digital','Bornes para cabos de até 25 mm²','Canaleta e trilho DIN','Sinaleiros luminosos','Seletora manual / desligado / automático','Botão de emergência','Componentes Coel, Altronic e Scame, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'220 V',
        'power_range'=>'Até 25 CV',
        'reference_code'=>'E.T_25CV_220V_MAN',
        'model'=>'Painel Estrela Triângulo 25CV',
        'price_cents'=>263100,
        'installments'=>3,
        'seo_title'=>'Painel Estrela Triângulo 25CV 220V com Amperímetro | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 25 CV 220 V com amperímetro digital, operação manual e automática, proteção IP54 e parada de emergência.',
        'source_slug'=>$amperimeter50Slug,
    ]);
}

$amperimeter20Slug = 'painel-estrela-triangulo-20cv-380v-manual-automatico-com-amperimetro';
$findAmperimeter20 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findAmperimeter20->execute(['slug'=>$amperimeter20Slug]);
if (!$findAmperimeter20->fetchColumn()) {
    $cloneAmperimeter20 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,:model,:price_cents,:installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,32,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $cloneAmperimeter20->execute([
        'name'=>'Painel Estrela Triângulo 20CV 380V Manual Automático',
        'slug'=>$amperimeter20Slug,
        'summary'=>'Painel estrela-triângulo para motores de até 20 CV em 380 V, projetado para operação manual ou automática por boia ou pressostato.',
        'description'=>'O Painel Estrela Triângulo 20CV 380V foi projetado para aplicações industriais que exigem controle eficiente e seguro de motores, com operação manual ou automática por sinal de boia ou pressostato. O conjunto oferece monitoramento visual do estado de operação, proteção contra falta de fase e sobrecarga, parada de emergência e bornes industriais para conexões estáveis. Os botões da porta e do quadro são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos utiliza flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual. A instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado.',
        'features'=>json_encode(['SKU: E.T_20CV_380V_MAN','Dimensões: 40 × 30 × 20 cm','Adesivo frontal','Botão duplo','Seletora manual / desligado / automático','Operação automática por boia ou pressostato','Parada de emergência','Amperímetro digital para monitoramento da corrente','LED de comando ligado e monitoramento','LED amarelo: sobrecarga','Tensão: 380 V','Potência máxima: 20 CV'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Alternância fácil entre os modos manual e automático','Monitoramento rápido do estado operacional e da corrente','Proteção integrada contra falhas elétricas','Componentes industriais de alta durabilidade','Bornes de qualidade para conexões estáveis e seguras','Montagem organizada para facilitar instalação e manutenção'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor geral','Barramento estrela-triângulo','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Amperímetro digital','Bornes para cabos de até 25 mm²','Canaleta e trilho DIN','Sinaleiros luminosos','Seletora manual / desligado / automático','Botão de emergência','Componentes Coel, Altronic e Scame, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'380 V',
        'power_range'=>'Até 20 CV',
        'reference_code'=>'E.T_20CV_380V_MAN',
        'model'=>'Painel Estrela Triângulo 20CV 380V',
        'price_cents'=>162100,
        'installments'=>3,
        'seo_title'=>'Painel Estrela Triângulo 20CV 380V com Amperímetro | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 20 CV 380 V com amperímetro digital, operação manual e automática, proteção IP54 e parada de emergência.',
        'source_slug'=>$amperimeter50Slug,
    ]);
}

$amperimeter15Slug = 'painel-estrela-triangulo-15cv-220v-manual-automatico-com-amperimetro';
$findAmperimeter15 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findAmperimeter15->execute(['slug'=>$amperimeter15Slug]);
if (!$findAmperimeter15->fetchColumn()) {
    $cloneAmperimeter15 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,:model,:price_cents,:installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,33,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $cloneAmperimeter15->execute([
        'name'=>'Painel Estrela Triângulo 15CV 220V Manual Automático',
        'slug'=>$amperimeter15Slug,
        'summary'=>'Painel estrela-triângulo para motores trifásicos de até 15 CV em 220 V, com operação manual ou automática por boia ou pressostato.',
        'description'=>'O Painel Estrela Triângulo 15CV 220V foi projetado para o acionamento manual ou automático de motores por sinal de boia ou pressostato com contato comum e normalmente aberto. Ideal para ambientes industriais, oferece controle eficiente, monitoramento da corrente e proteção para motores trifásicos. Os botões da porta e do quadro são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos utiliza flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual. A instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado.',
        'features'=>json_encode(['SKU: E.T_15CV_220V_MAN','Dimensões: 40 × 30 × 20 cm','Adesivo frontal','Seletora manual / desligado / automático','Operação automática por boia ou pressostato (comum / NA)','Parada de emergência','Amperímetro digital para monitoramento da corrente','LED de comando ligado e monitoramento','LED amarelo: sobrecarga','Tensão: 220 V','Potência máxima: 15 CV'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Alternância entre operação manual e automática','Parada de emergência e proteção contra sobrecarga','Monitoramento rápido do estado operacional e da corrente','Componentes industriais de alta durabilidade','Bornes robustos para cabos de até 25 mm²','Montagem organizada para facilitar instalação e manutenção'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor geral','Barramento estrela-triângulo','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Amperímetro digital','Bornes para cabos de até 25 mm²','Canaleta e trilho DIN','Sinaleiros luminosos','Seletora manual / desligado / automático','Botão de emergência','Componentes Coel, Altronic e Scame, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'220 V',
        'power_range'=>'Até 15 CV',
        'reference_code'=>'E.T_15CV_220V_MAN',
        'model'=>'Painel Estrela Triângulo 15CV 220V',
        'price_cents'=>170100,
        'installments'=>3,
        'seo_title'=>'Painel Estrela Triângulo 15CV 220V com Amperímetro | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 15 CV 220 V com amperímetro digital, operação manual e automática por boia ou pressostato, proteção IP54 e parada de emergência.',
        'source_slug'=>$amperimeter50Slug,
    ]);
}

$amperimeter12_220Slug = 'painel-estrela-triangulo-12-5cv-220v-manual-automatico-com-amperimetro';
$findAmperimeter12_220 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findAmperimeter12_220->execute(['slug'=>$amperimeter12_220Slug]);
if (!$findAmperimeter12_220->fetchColumn()) {
    $cloneAmperimeter12_220 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,:model,:price_cents,:installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,34,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $cloneAmperimeter12_220->execute([
        'name'=>'Painel Estrela Triângulo 12,5CV 220V Manual Automático',
        'slug'=>$amperimeter12_220Slug,
        'summary'=>'Painel estrela-triângulo compacto para compressores e motores de até 12,5 CV em 220 V, com operação manual ou automática por boia ou pressostato.',
        'description'=>'O Painel Estrela Triângulo 12,5CV 220V foi projetado para acionar compressores e motores trifásicos, permitindo operação manual ou automática por sinal de boia ou pressostato. A solução combina eficiência, monitoramento da corrente e segurança em um conjunto compacto, com proteção contra falta de fase, sobrecarga e parada de emergência. Os botões da porta e do quadro são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos utiliza flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual. A instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado.',
        'features'=>json_encode(['SKU: E.T_12,5CV_220V_MAN','Dimensões: 40 × 30 × 20 cm','Adesivo frontal','Seletora manual / desligado / automático','Operação automática por boia ou pressostato','Parada de emergência','Amperímetro digital para monitoramento da corrente','Indicação luminosa de comando ligado','LED amarelo: sobrecarga','Tensão: 220 V','Potência máxima: 12,5 CV'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Alternância entre os modos manual e automático','Monitoramento contínuo do estado operacional e da corrente','Parada de emergência para proteção adicional','Componentes industriais de alta qualidade','Bornes robustos para facilitar instalação e manutenção','Solução compacta, confiável e versátil para compressores'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor geral','Barramento estrela-triângulo','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Amperímetro digital','Bornes para cabos de até 25 mm²','Canaleta e trilho DIN','Sinaleiros luminosos','Seletora manual / desligado / automático','Botão de emergência','Componentes Coel, Altronic e Scame, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'220 V',
        'power_range'=>'Até 12,5 CV',
        'reference_code'=>'E.T_12,5CV_220V_MAN',
        'model'=>'Painel Estrela Triângulo 12,5CV 220V',
        'price_cents'=>165200,
        'installments'=>3,
        'seo_title'=>'Painel Estrela Triângulo 12,5CV 220V com Amperímetro | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 12,5 CV 220 V com amperímetro digital, operação manual e automática por boia ou pressostato, proteção IP54 e parada de emergência.',
        'source_slug'=>$amperimeter50Slug,
    ]);
}

$amperimeter10_380Slug = 'painel-estrela-triangulo-10cv-380v-manual-automatico-com-amperimetro';
$findAmperimeter10_380 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findAmperimeter10_380->execute(['slug'=>$amperimeter10_380Slug]);
if (!$findAmperimeter10_380->fetchColumn()) {
    $cloneAmperimeter10_380 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,:model,:price_cents,:installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,35,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $cloneAmperimeter10_380->execute([
        'name'=>'Painel Estrela Triângulo 10CV 380V Manual Automático',
        'slug'=>$amperimeter10_380Slug,
        'summary'=>'Painel estrela-triângulo para motores de até 10 CV em 380 V, com operação manual ou automática por boia ou pressostato.',
        'description'=>'O Painel Estrela Triângulo 10CV 380V foi projetado para o acionamento seguro e eficiente de motores em aplicações industriais e comerciais, com operação manual ou automática por sinal de boia ou pressostato. O conjunto oferece monitoramento da corrente e do estado de operação, proteção contra falta de fase e sobrecarga, parada de emergência e conexões industriais confiáveis. Os botões da porta e do quadro são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos utiliza flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual. A instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado.',
        'features'=>json_encode(['SKU: E.T_10CV_380V_MAN','Dimensões: 40 × 30 × 20 cm','Adesivo frontal','Seletora manual / desligado / automático','Operação automática por boia ou pressostato','Parada de emergência','Amperímetro digital para monitoramento da corrente','LED de comando ligado','LED amarelo: sobrecarga','Tensão: 380 V','Potência máxima: 10 CV'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Alternância fácil entre os modos manual e automático','Parada de emergência e proteção contra sobrecarga','Monitoramento rápido do estado operacional e da corrente','Componentes industriais de alta durabilidade','Bornes de qualidade para facilitar a conexão dos cabos','Solução confiável para aplicações industriais e comerciais'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor geral','Barramento estrela-triângulo','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Amperímetro digital','Bornes para cabos de até 25 mm²','Canaleta e trilho DIN','Sinaleiros luminosos','Seletora manual / desligado / automático','Botão de emergência','Componentes Scame, Coel e Altronic, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'380 V',
        'power_range'=>'Até 10 CV',
        'reference_code'=>'E.T_10CV_380V_MAN',
        'model'=>'Painel Estrela Triângulo 10CV 380V',
        'price_cents'=>149400,
        'installments'=>3,
        'seo_title'=>'Painel Estrela Triângulo 10CV 380V com Amperímetro | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 10 CV 380 V com amperímetro digital, operação manual e automática por boia ou pressostato, proteção IP54 e parada de emergência.',
        'source_slug'=>$amperimeter50Slug,
    ]);
}

$amperimeter10_220Slug = 'painel-estrela-triangulo-10cv-220v-manual-automatico-com-amperimetro';
$findAmperimeter10_220 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findAmperimeter10_220->execute(['slug'=>$amperimeter10_220Slug]);
if (!$findAmperimeter10_220->fetchColumn()) {
    $cloneAmperimeter10_220 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,:model,:price_cents,:installments,stock_status,stock_quantity,lead_time,sales_channel,:warranty_days,36,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $cloneAmperimeter10_220->execute([
        'name'=>'Painel Estrela Triângulo 10CV 220V Manual Automático',
        'slug'=>$amperimeter10_220Slug,
        'summary'=>'Painel estrela-triângulo para motores de até 10 CV em 220 V, com operação manual ou automática por boia ou pressostato.',
        'description'=>'O Painel Estrela Triângulo 10CV 220V foi projetado para o acionamento eficiente de motores em aplicações industriais e comerciais, permitindo operação manual ou automática por sinal de boia ou pressostato. O conjunto oferece monitoramento da corrente e do estado de operação, proteção contra falta de fase e sobrecarga, parada de emergência e organização interna que facilita a manutenção. Os botões da porta e do quadro são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos utiliza flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual. A instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado.',
        'features'=>json_encode(['SKU: E.T_10CV_220V_MAN','Dimensões: 40 × 30 × 20 cm','Adesivo frontal','Seletora manual / desligado / automático','Operação automática por boia ou pressostato','Parada de emergência','Amperímetro digital para monitoramento da corrente','LED de comando ligado','LED amarelo: sobrecarga','Tensão: 220 V','Potência máxima: 10 CV','Garantia: 7 dias'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Escolha entre operação manual e automática','Parada de emergência e proteção contra sobrecarga','Monitoramento rápido do estado operacional e da corrente','Componentes industriais de alta durabilidade','Bornes para cabos de até 25 mm²','Organização interna que facilita a instalação e a manutenção'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor geral','Barramento estrela-triângulo','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Amperímetro digital','Bornes para cabos de até 25 mm²','Canaleta e trilho DIN','Sinaleiros luminosos','Seletora manual / desligado / automático','Botão de emergência','Componentes Coel, Altronic e Scame, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'220 V',
        'power_range'=>'Até 10 CV',
        'reference_code'=>'E.T_10CV_220V_MAN',
        'model'=>'Painel Estrela Triângulo',
        'price_cents'=>160700,
        'installments'=>3,
        'warranty_days'=>7,
        'seo_title'=>'Painel Estrela Triângulo 10CV 220V com Amperímetro | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 10 CV 220 V com amperímetro digital, operação manual e automática por boia ou pressostato, proteção IP54 e parada de emergência.',
        'source_slug'=>$amperimeter50Slug,
    ]);
}

$amperimeter7_380Slug = 'painel-estrela-triangulo-7-5cv-380v-manual-automatico-com-amperimetro';
$findAmperimeter7_380 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findAmperimeter7_380->execute(['slug'=>$amperimeter7_380Slug]);
if (!$findAmperimeter7_380->fetchColumn()) {
    $cloneAmperimeter7_380 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,:model,:price_cents,:installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,37,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $cloneAmperimeter7_380->execute([
        'name'=>'Painel Estrela Triângulo 7,5CV 380V Manual Automático',
        'slug'=>$amperimeter7_380Slug,
        'summary'=>'Painel estrela-triângulo para motores de até 7,5 CV em 380 V, com operação manual ou automática por boia ou pressostato comum/NA.',
        'description'=>'O Painel Estrela Triângulo 7,5CV 380V foi projetado para o acionamento manual ou automático de motores por sinal de boia ou pressostato com contato comum e normalmente aberto. A solução oferece controle eficiente, monitoramento da corrente e segurança para aplicações industriais, com proteção contra falta de fase e sobrecarga e parada de emergência. Os botões da porta e do quadro são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos utiliza flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual. A instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado.',
        'features'=>json_encode(['SKU: E.T_7,5CV_380V_MAN','Dimensões: 40 × 30 × 20 cm','Adesivo frontal','Seletora manual / desligado / automático','Operação automática por boia ou pressostato (comum / NA)','Parada de emergência','Amperímetro digital para monitoramento da corrente','LED de comando ligado e monitoramento','LED amarelo: sobrecarga','Tensão: 380 V','Potência máxima: 7,5 CV'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Acionamento manual e automático para maior versatilidade','Parada de emergência e proteção contra sobrecarga','Monitoramento rápido do estado operacional e da corrente','Componentes industriais de alta durabilidade','Bornes robustos para cabos de até 25 mm²','Solução prática e confiável para aplicações industriais'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor geral','Barramento estrela-triângulo','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Amperímetro digital','Bornes para cabos de até 25 mm²','Canaleta e trilho DIN','Sinaleiros luminosos','Seletora manual / desligado / automático','Botão de emergência','Componentes Scame, Coel e Altronic, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'380 V',
        'power_range'=>'Até 7,5 CV',
        'reference_code'=>'E.T_7,5CV_380V_MAN',
        'model'=>'Painel Estrela Triângulo 7,5CV 380V',
        'price_cents'=>149400,
        'installments'=>3,
        'seo_title'=>'Painel Estrela Triângulo 7,5CV 380V com Amperímetro | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 7,5 CV 380 V com amperímetro digital, operação manual e automática por boia ou pressostato, proteção IP54 e parada de emergência.',
        'source_slug'=>$amperimeter50Slug,
    ]);
}

$amperimeter7_220Slug = 'painel-estrela-triangulo-compressor-7-5cv-220v-manual-automatico-com-amperimetro';
$findAmperimeter7_220 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findAmperimeter7_220->execute(['slug'=>$amperimeter7_220Slug]);
if (!$findAmperimeter7_220->fetchColumn()) {
    $cloneAmperimeter7_220 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,:model,:price_cents,:installments,stock_status,stock_quantity,lead_time,sales_channel,:warranty_days,38,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $cloneAmperimeter7_220->execute([
        'name'=>'Painel Estrela Triângulo Compressor 7,5CV 220V Manual Automático',
        'slug'=>$amperimeter7_220Slug,
        'summary'=>'Painel estrela-triângulo para compressores e motores de até 7,5 CV em 220 V, com operação manual ou automática por boia ou pressostato comum/NA.',
        'description'=>'O Painel Estrela Triângulo Compressor 7,5CV 220V foi projetado para o acionamento manual ou automático de motores por sinal de boia ou pressostato com contato comum e normalmente aberto. Oferece controle eficiente e seguro para aplicações industriais e comerciais, com monitoramento da corrente, proteção contra falta de fase e sobrecarga e parada de emergência. Os botões da porta e do quadro são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos utiliza flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual. A instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado. Os componentes podem variar conforme a disponibilidade em estoque, mantendo sempre a qualidade e a funcionalidade do sistema.',
        'features'=>json_encode(['SKU: E.T_7,5CV_220V_MAN','Dimensões: 40 × 30 × 20 cm','Adesivo frontal','Seletora manual / desligado / automático','Operação automática por boia ou pressostato (comum / NA)','Parada de emergência','Amperímetro digital para monitoramento da corrente','LED de comando ligado','LED amarelo: sobrecarga','Tensão: 220 V','Potência máxima: 7,5 CV','Garantia: 7 dias'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Acionamento manual e automático para maior flexibilidade','Parada de emergência e proteção contra sobrecarga','Monitoramento rápido do estado operacional e da corrente','Componentes industriais de alta qualidade','Bornes robustos para cabos de até 25 mm²','Solução segura e eficiente para compressores e motores'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor geral','Barramento estrela-triângulo','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Amperímetro digital','Bornes para cabos de até 25 mm²','Canaleta e trilho DIN','Sinaleiros luminosos','Seletora manual / desligado / automático','Botão de emergência','Componentes Coel, Altronic e Scame, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'220 V',
        'power_range'=>'Até 7,5 CV',
        'reference_code'=>'E.T_7,5CV_220V_MAN',
        'model'=>'Painel Estrela Triângulo',
        'price_cents'=>152800,
        'installments'=>3,
        'warranty_days'=>7,
        'seo_title'=>'Painel Estrela Triângulo Compressor 7,5CV 220V com Amperímetro | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo para compressor de 7,5 CV em 220 V, com amperímetro digital, operação manual e automática, proteção IP54 e parada de emergência.',
        'source_slug'=>$amperimeter50Slug,
    ]);
}

$pushButton50Slug = 'painel-estrela-triangulo-50cv-380v-botoeiras';
$findPushButton50 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findPushButton50->execute(['slug'=>$pushButton50Slug]);
if (!$findPushButton50->fetchColumn()) {
    $clonePushButton50 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,:model,:price_cents,:installments,stock_status,stock_quantity,lead_time,sales_channel,:warranty_days,39,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $clonePushButton50->execute([
        'name'=>'Painel Estrela Triângulo 50CV 380V Botoeiras',
        'slug'=>$pushButton50Slug,
        'summary'=>'Painel estrela-triângulo de 50 CV em 380 V com botoeiras, desenvolvido para a partida segura de compressores, bombas e motores trifásicos.',
        'description'=>'O Painel Estrela Triângulo 50CV 380V com botoeiras foi desenvolvido para a partida segura e eficiente de compressores, bombas e motores trifásicos em aplicações industriais pesadas. A partida estrela-triângulo reduz os picos de corrente, protege o motor e aumenta a durabilidade do conjunto. É indicado para compressores industriais, betoneiras, fornos, esteiras, portos, aeroportos, estádios, canteiros de obras, eventos e outros equipamentos trifásicos. Não é recomendado para partidas com carga inicial; para essas aplicações, consulte a engenharia. Os botões da porta e do quadro são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos utiliza flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual. A instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado.',
        'features'=>json_encode(['SKU: E.T_50CV_380V_BOT','Número de fases: trifásico','Ligação de motores: trifásica','Potência máxima: 50 CV','Tensão: 380 V','Frequência: 60 Hz','Faixa de corrente térmica: 40 A – 50 A','A corrente do relé térmico é dividida por dois devido à comutação dos contatores','Grau de proteção: IP54','Dimensões: 60 × 50 × 25 cm','Adesivo frontal incluso','Seletora de 3 posições: manual / desligado / automático','Comando por botoeiras','Parada de emergência','LED vermelho: comando ligado','LED amarelo: sobrecarga'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Diagrama elétrico completo facilita a manutenção e a compreensão do circuito','Sistema de aterramento reforça a segurança da instalação','Cabos numerados por anilhas para identificação rápida','Componentes industriais de alta qualidade e confiabilidade','Suporte técnico para orientações de uso geral','Guia de ligação de motores para conexões corretas','Ligação rápida por bornes','Montagem profissional com acabamento organizado'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor termomagnético geral','Barramento de potência','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Canaleta de cabeamento','Trilho DIN','Sinaleiros luminosos','Botoeiras liga e desliga','Seletora de três posições','Botão de emergência','Componentes Altronic, Siemens, Sibratec e Lukma, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'380 V trifásico',
        'power_range'=>'Até 50 CV',
        'reference_code'=>'E.T_50CV_380V_BOT',
        'model'=>'Painel Estrela Triângulo 50CV',
        'price_cents'=>327800,
        'installments'=>3,
        'warranty_days'=>365,
        'seo_title'=>'Painel Estrela Triângulo 50CV 380V Botoeiras | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 50 CV 380 V com botoeiras, proteção IP54, operação manual e automática, sinaleiros e parada de emergência.',
        'source_slug'=>$pushButton15Slug,
    ]);
}

$pushButton30Slug = 'painel-estrela-triangulo-30cv-380v-botoeiras';
$findPushButton30 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findPushButton30->execute(['slug'=>$pushButton30Slug]);
if (!$findPushButton30->fetchColumn()) {
    $clonePushButton30 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,:model,:price_cents,:installments,stock_status,stock_quantity,lead_time,sales_channel,:warranty_days,40,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $clonePushButton30->execute([
        'name'=>'Painel Estrela Triângulo 30CV 380V Botoeiras',
        'slug'=>$pushButton30Slug,
        'summary'=>'Painel estrela-triângulo de 30 CV em 380 V com botoeiras, desenvolvido para a partida segura de compressores, bombas e motores trifásicos.',
        'description'=>'O Painel Estrela Triângulo 30CV 380V com botoeiras foi desenvolvido para a partida segura e eficiente de compressores, bombas e motores trifásicos em aplicações industriais exigentes. A partida estrela-triângulo reduz os picos de corrente, protege o motor e aumenta a durabilidade do conjunto. É indicado para compressores industriais, betoneiras, fornos, esteiras, portos, aeroportos, estádios, canteiros de obras, eventos e outros equipamentos trifásicos. Não é recomendado para partidas com carga inicial; para essas aplicações, consulte a engenharia. Os botões da porta e do quadro são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos utiliza flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual. A instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado.',
        'features'=>json_encode(['SKU: E.T_30CV_380V_BOT','Número de fases: trifásico','Ligação de motores: trifásica','Potência máxima: 30 CV','Tensão: 380 V','Frequência: 60 Hz','Faixa de corrente térmica: 25 A – 32 A','A corrente do relé térmico é dividida por dois devido à comutação dos contatores','Grau de proteção: IP54','Dimensões: 40 × 30 × 20 cm','Adesivo frontal incluso','Seletora de 3 posições: manual / desligado / automático','Comando por botoeiras','Parada de emergência','LED vermelho: comando ligado','LED amarelo: sobrecarga'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Diagrama elétrico completo facilita a manutenção e a compreensão do circuito','Sistema de aterramento aumenta a segurança da instalação','Cabos numerados por anilhas para identificação rápida','Componentes industriais de alta qualidade e confiabilidade','Suporte técnico para orientações de uso geral','Guia de ligação de motores para conexões corretas','Ligação rápida por bornes','Montagem profissional com acabamento organizado'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor termomagnético geral','Barramento de potência','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Canaleta de cabeamento','Trilho DIN','Sinaleiros luminosos','Botoeiras liga e desliga','Seletora de três posições','Botão de emergência','Componentes Altronic, Siemens, Sibratec e Lukma, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'380 V trifásico',
        'power_range'=>'Até 30 CV',
        'reference_code'=>'E.T_30CV_380V_BOT',
        'model'=>'Painel Estrela Triângulo',
        'price_cents'=>171800,
        'installments'=>3,
        'warranty_days'=>365,
        'seo_title'=>'Painel Estrela Triângulo 30CV 380V Botoeiras | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 30 CV 380 V com botoeiras, proteção IP54, operação manual e automática, sinaleiros e parada de emergência.',
        'source_slug'=>$pushButton50Slug,
    ]);
}

$pushButton25Slug = 'painel-estrela-triangulo-25cv-380v-botoeiras';
$findPushButton25 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findPushButton25->execute(['slug'=>$pushButton25Slug]);
if (!$findPushButton25->fetchColumn()) {
    $clonePushButton25 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,:model,:price_cents,:installments,stock_status,stock_quantity,lead_time,sales_channel,:warranty_days,41,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $clonePushButton25->execute([
        'name'=>'Painel Estrela Triângulo 25CV 380V Botoeiras',
        'slug'=>$pushButton25Slug,
        'summary'=>'Painel estrela-triângulo de 25 CV em 380 V com botoeiras, desenvolvido para a partida segura de compressores, bombas e motores trifásicos.',
        'description'=>'O Painel Estrela Triângulo 25CV 380V com botoeiras foi desenvolvido para a partida segura e eficiente de compressores, bombas e motores trifásicos em aplicações industriais exigentes. A partida estrela-triângulo reduz os picos de corrente, protege o motor e aumenta a durabilidade do conjunto. É indicado para compressores industriais, betoneiras, fornos, esteiras, portos, aeroportos, estádios, canteiros de obras, eventos e outros equipamentos trifásicos. Não é recomendado para partidas com carga inicial; para essas aplicações, consulte a engenharia. Os botões da porta e do quadro são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos utiliza flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual. A instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado.',
        'features'=>json_encode(['SKU: E.T_25CV_380V_BOT','Número de fases: trifásico','Ligação de motores: trifásica','Potência máxima: 25 CV','Tensão: 380 V','Frequência: 60 Hz','Faixa de corrente térmica: 25 A – 32 A','A corrente do relé térmico é dividida por dois devido à comutação dos contatores','Grau de proteção: IP54','Dimensões: 40 × 30 × 20 cm','Adesivo frontal incluso','Seletora de 3 posições: manual / desligado / automático','Comando por botoeiras','Parada de emergência','LED vermelho: comando ligado','LED amarelo: sobrecarga'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Diagrama elétrico completo facilita a manutenção e a compreensão do circuito','Sistema de aterramento aumenta a segurança da instalação','Cabos numerados por anilhas para identificação rápida','Componentes industriais de alta qualidade e confiabilidade','Suporte técnico para orientações de uso geral','Guia de ligação de motores para conexões corretas','Ligação rápida por bornes','Montagem profissional com acabamento organizado'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor termomagnético geral','Barramento de potência','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Canaleta de cabeamento','Trilho DIN','Sinaleiros luminosos','Botoeiras liga e desliga','Seletora de três posições','Botão de emergência','Componentes Altronic, Siemens, Sibratec e Lukma, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'380 V trifásico',
        'power_range'=>'Até 25 CV',
        'reference_code'=>'E.T_25CV_380V_BOT',
        'model'=>'Painel Estrela Triângulo',
        'price_cents'=>170600,
        'installments'=>3,
        'warranty_days'=>365,
        'seo_title'=>'Painel Estrela Triângulo 25CV 380V Botoeiras | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 25 CV 380 V com botoeiras, proteção IP54, operação manual e automática, sinaleiros e parada de emergência.',
        'source_slug'=>$pushButton30Slug,
    ]);
}

$pushButton20Slug = 'painel-estrela-triangulo-20cv-380v-botoeiras';
$findPushButton20 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findPushButton20->execute(['slug'=>$pushButton20Slug]);
if (!$findPushButton20->fetchColumn()) {
    $clonePushButton20 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,:model,:price_cents,:installments,stock_status,stock_quantity,lead_time,sales_channel,:warranty_days,42,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $clonePushButton20->execute([
        'name'=>'Painel Estrela Triângulo 20CV 380V Botoeiras',
        'slug'=>$pushButton20Slug,
        'summary'=>'Painel estrela-triângulo de 20 CV em 380 V com botoeiras, desenvolvido para a partida segura de compressores, bombas e motores trifásicos.',
        'description'=>'O Painel Estrela Triângulo 20CV 380V com botoeiras foi desenvolvido para a partida segura e eficiente de compressores, bombas e motores trifásicos em ambientes industriais exigentes. A partida estrela-triângulo reduz os picos de corrente, protege o motor e aumenta a durabilidade do conjunto. É indicado para compressores industriais, betoneiras, fornos, esteiras, portos, aeroportos, estádios, canteiros de obras, eventos e outros equipamentos trifásicos. Não é recomendado para partidas com carga inicial; para essas aplicações, consulte a engenharia. Os botões da porta e do quadro são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos utiliza flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual. A instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado.',
        'features'=>json_encode(['SKU: E.T_20CV_380V_BOT','Número de fases: trifásico','Ligação de motores: trifásica','Potência máxima: 20 CV','Tensão: 380 V','Frequência: 60 Hz','Faixa de corrente térmica: 25 A – 32 A','A corrente do relé térmico é dividida por dois devido à comutação dos contatores','Grau de proteção: IP54','Dimensões: 40 × 30 × 20 cm','Adesivo frontal incluso','Seletora de 3 posições: manual / desligado / automático','Comando por botoeiras','Parada de emergência','LED vermelho: comando ligado','LED amarelo: sobrecarga'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Diagrama elétrico completo facilita a manutenção e a compreensão do circuito','Sistema de aterramento aumenta a segurança da instalação','Cabos numerados por anilhas para identificação rápida','Componentes industriais de alta qualidade e confiabilidade','Suporte técnico para orientações de uso geral','Guia de ligação de motores para conexões corretas','Ligação rápida por bornes','Montagem profissional com acabamento organizado'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor termomagnético geral','Barramento de potência','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Canaleta de cabeamento','Trilho DIN','Sinaleiros luminosos','Botoeiras liga e desliga','Seletora de três posições','Botão de emergência','Componentes Altronic, Siemens, Sibratec e Lukma, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'380 V trifásico',
        'power_range'=>'Até 20 CV',
        'reference_code'=>'E.T_20CV_380V_BOT',
        'model'=>'Painel Estrela Triângulo',
        'price_cents'=>163700,
        'installments'=>3,
        'warranty_days'=>365,
        'seo_title'=>'Painel Estrela Triângulo 20CV 380V Botoeiras | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 20 CV 380 V com botoeiras, proteção IP54, operação manual e automática, sinaleiros e parada de emergência.',
        'source_slug'=>$pushButton25Slug,
    ]);
}

$pushButton15_380Slug = 'painel-estrela-triangulo-15cv-380v-botoeiras';
$findPushButton15_380 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findPushButton15_380->execute(['slug'=>$pushButton15_380Slug]);
if (!$findPushButton15_380->fetchColumn()) {
    $clonePushButton15_380 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,:model,:price_cents,:installments,stock_status,stock_quantity,lead_time,sales_channel,:warranty_days,43,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $clonePushButton15_380->execute([
        'name'=>'Painel Estrela Triângulo 15CV 380V Botoeiras',
        'slug'=>$pushButton15_380Slug,
        'summary'=>'Painel estrela-triângulo de 15 CV em 380 V com botoeiras, desenvolvido para a partida segura de compressores, bombas e motores trifásicos.',
        'description'=>'O Painel Estrela Triângulo 15CV 380V com botoeiras foi desenvolvido para a partida segura e eficiente de compressores, bombas e motores trifásicos em ambientes industriais. A partida estrela-triângulo reduz os picos de corrente, protege o motor e aumenta a durabilidade do conjunto. É indicado para compressores industriais, betoneiras, fornos, esteiras, portos, aeroportos, estádios, canteiros de obras, eventos e outros equipamentos trifásicos. Não é recomendado para partidas com carga inicial; para essas aplicações, consulte a engenharia. Os botões da porta e do quadro são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos utiliza flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual. A instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado.',
        'features'=>json_encode(['SKU: E.T_15CV_380V_BOT','Número de fases: trifásico','Ligação de motores: trifásica','Potência máxima: 15 CV','Tensão: 380 V','Frequência: 60 Hz','Faixa de corrente térmica: 20 A – 25 A','A corrente do relé térmico é dividida por dois devido à comutação dos contatores','Grau de proteção: IP54','Dimensões: 40 × 30 × 20 cm','Adesivo frontal incluso','Seletora de 3 posições: manual / desligado / automático','Comando por botoeiras','Parada de emergência','LED vermelho: comando ligado','LED amarelo: sobrecarga'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Diagrama elétrico completo facilita a manutenção e a compreensão do circuito','Sistema de aterramento aumenta a segurança da instalação','Cabos numerados por anilhas para identificação rápida','Componentes industriais de alta qualidade e confiabilidade','Suporte técnico para orientações de uso geral','Guia de ligação de motores para conexões corretas','Ligação rápida por bornes','Montagem profissional com acabamento organizado'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor termomagnético geral','Barramento de potência','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Canaleta de cabeamento','Trilho DIN','Sinaleiros luminosos','Botoeiras liga e desliga','Seletora de três posições','Botão de emergência','Componentes Altronic, Siemens, Sibratec e Lukma, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'380 V trifásico',
        'power_range'=>'Até 15 CV',
        'reference_code'=>'E.T_15CV_380V_BOT',
        'model'=>'Painel Estrela Triângulo',
        'price_cents'=>162800,
        'installments'=>3,
        'warranty_days'=>365,
        'seo_title'=>'Painel Estrela Triângulo 15CV 380V Botoeiras | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 15 CV 380 V com botoeiras, proteção IP54, operação manual e automática, sinaleiros e parada de emergência.',
        'source_slug'=>$pushButton20Slug,
    ]);
}

$pushButton10_380Slug = 'painel-estrela-triangulo-10cv-380v-botoeiras';
$findPushButton10_380 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findPushButton10_380->execute(['slug'=>$pushButton10_380Slug]);
if (!$findPushButton10_380->fetchColumn()) {
    $clonePushButton10_380 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,:model,:price_cents,:installments,stock_status,stock_quantity,lead_time,sales_channel,:warranty_days,44,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $clonePushButton10_380->execute([
        'name'=>'Painel Estrela Triângulo 10CV 380V Botoeiras',
        'slug'=>$pushButton10_380Slug,
        'summary'=>'Painel estrela-triângulo de 10 CV em 380 V com botoeiras, desenvolvido para o acionamento seguro de compressores, bombas e motores trifásicos.',
        'description'=>'O Painel Estrela Triângulo 10CV 380V com botoeiras é uma solução eficiente, segura e durável para o acionamento de compressores, bombas e motores trifásicos. A partida estrela-triângulo reduz os picos de corrente, protege o motor e facilita a operação em aplicações industriais. É indicado para compressores, betoneiras, fornos industriais, esteiras, aeroportos, estádios, shows, portos e outros equipamentos trifásicos. Não é recomendado para partidas que iniciam com carga; para essas aplicações, consulte a engenharia. Os botões da porta e do quadro são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos utiliza flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual. A instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado.',
        'features'=>json_encode(['SKU: E.T_10CV_380V_BOT','Número de fases: trifásico','Ligação de motores: trifásica','Potência máxima: 10 CV','Tensão: 380 V','Frequência: 60 Hz','Faixa de corrente térmica: 12,5 A – 18 A','A corrente do relé térmico é dividida por dois devido à comutação dos contatores','Grau de proteção: IP54','Dimensões: 40 × 30 × 20 cm','Adesivo frontal incluso','Seletora de 3 posições: manual / desligado / automático','Comando por botoeiras','Parada de emergência','LED vermelho: comando ligado','LED amarelo: sobrecarga'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Diagrama elétrico facilita a compreensão e a manutenção do circuito','Sistema de aterramento reforça a segurança da instalação','Cabos numerados por anilhas para identificação rápida','Componentes industriais de alta qualidade e confiabilidade','Suporte técnico para orientações de uso geral','Guia de ligação de motores para conexões corretas','Ligação rápida por bornes','Montagem profissional com acabamento organizado'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor termomagnético geral','Barramento de potência','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Canaleta de cabeamento','Trilho DIN','Quadro metálico','Sinaleiros luminosos','Botoeiras liga e desliga','Seletora de três posições','Botão de emergência','Componentes Altronic, Siemens, Sibratec e Lukma, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'380 V trifásico',
        'power_range'=>'Até 10 CV',
        'reference_code'=>'E.T_10CV_380V_BOT',
        'model'=>'Painel Estrela Triângulo',
        'price_cents'=>150900,
        'installments'=>3,
        'warranty_days'=>365,
        'seo_title'=>'Painel Estrela Triângulo 10CV 380V Botoeiras | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 10 CV 380 V com botoeiras, proteção IP54, operação manual e automática, sinaleiros e parada de emergência.',
        'source_slug'=>$pushButton15_380Slug,
    ]);
}

$pushButton7_380Slug = 'painel-estrela-triangulo-7-5cv-380v-botoeiras';
$findPushButton7_380 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findPushButton7_380->execute(['slug'=>$pushButton7_380Slug]);
if (!$findPushButton7_380->fetchColumn()) {
    $clonePushButton7_380 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,:model,:price_cents,:installments,stock_status,stock_quantity,lead_time,sales_channel,:warranty_days,45,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $clonePushButton7_380->execute([
        'name'=>'Painel Estrela Triângulo 7,5CV 380V Botoeiras',
        'slug'=>$pushButton7_380Slug,
        'summary'=>'Painel estrela-triângulo de 7,5 CV em 380 V com botoeiras, desenvolvido para a partida segura de compressores, bombas e motores trifásicos.',
        'description'=>'O Painel Estrela Triângulo 7,5CV 380V com botoeiras foi desenvolvido para a partida segura e eficiente de compressores, bombas e motores trifásicos em ambientes industriais. A partida estrela-triângulo reduz os picos de corrente, protege o motor e aumenta a durabilidade do conjunto. É indicado para compressores industriais, betoneiras, fornos, esteiras, portos, aeroportos, estádios, canteiros de obras, eventos e outros equipamentos trifásicos. Não é recomendado para partidas com carga inicial; para essas aplicações, consulte a engenharia. Os botões da porta e do quadro são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos utiliza flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual. A instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado.',
        'features'=>json_encode(['SKU: E.T_7,5CV_380V_BOT','Número de fases: trifásico','Ligação de motores: trifásica','Potência máxima: 7,5 CV','Tensão: 380 V','Frequência: 60 Hz','Faixa de corrente térmica: 12,5 A – 18 A','A corrente do relé térmico é dividida por dois devido à comutação dos contatores','Grau de proteção: IP54','Dimensões: 40 × 30 × 20 cm','Adesivo frontal incluso','Seletora de 3 posições: manual / desligado / automático','Comando por botoeiras','Parada de emergência','LED vermelho: comando ligado','LED amarelo: sobrecarga'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Diagrama elétrico completo facilita a manutenção e a compreensão do circuito','Sistema de aterramento aumenta a segurança da instalação','Cabos numerados por anilhas para identificação rápida','Componentes industriais de alta qualidade e confiabilidade','Suporte técnico para orientações de uso geral','Guia de ligação de motores para conexões corretas','Ligação rápida por bornes','Montagem profissional com acabamento organizado'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor termomagnético geral','Barramento de potência','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Canaleta de cabeamento','Trilho DIN','Sinaleiros luminosos','Botoeiras liga e desliga','Seletora de três posições','Botão de emergência','Componentes Altronic, Siemens, Sibratec e Lukma, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'380 V trifásico',
        'power_range'=>'Até 7,5 CV',
        'reference_code'=>'E.T_7,5CV_380V_BOT',
        'model'=>'Painel Estrela Triângulo',
        'price_cents'=>150900,
        'installments'=>3,
        'warranty_days'=>365,
        'seo_title'=>'Painel Estrela Triângulo 7,5CV 380V Botoeiras | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 7,5 CV 380 V com botoeiras, proteção IP54, operação manual e automática, sinaleiros e parada de emergência.',
        'source_slug'=>$pushButton10_380Slug,
    ]);
}

$pushButton50_220Slug = 'painel-estrela-triangulo-50cv-220v-botoeiras';
$findPushButton50_220 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findPushButton50_220->execute(['slug'=>$pushButton50_220Slug]);
if (!$findPushButton50_220->fetchColumn()) {
    $clonePushButton50_220 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,:model,:price_cents,:installments,stock_status,stock_quantity,lead_time,sales_channel,:warranty_days,46,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $clonePushButton50_220->execute([
        'name'=>'Painel Estrela Triângulo 50CV 220V Botoeiras',
        'slug'=>$pushButton50_220Slug,
        'summary'=>'Painel estrela-triângulo de 50 CV em 220 V com botoeiras, desenvolvido para a partida segura de compressores, bombas e motores trifásicos.',
        'description'=>'O Painel Estrela Triângulo 50CV 220V com botoeiras foi desenvolvido para a partida segura e eficiente de compressores, bombas e motores trifásicos em ambientes industriais. A partida estrela-triângulo reduz os picos de corrente, aumenta a proteção do motor e prolonga a vida útil do conjunto. É indicado para compressores industriais, betoneiras, fornos, esteiras, portos, aeroportos, estádios, canteiros de obras, eventos e outros equipamentos trifásicos. Não é recomendado para partidas sob carga inicial; para essas aplicações, consulte a engenharia. Os botões da porta e do quadro são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos utiliza flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual. A instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado.',
        'features'=>json_encode(['SKU: E.T_50CV_220V_BOT','Número de fases: trifásico','Ligação de motores: trifásica','Potência máxima: 50 CV','Tensão: 220 V','Frequência: 60 Hz','Faixa de corrente térmica: 40 A – 50 A','A corrente do relé térmico é dividida por dois devido à comutação dos contatores','Grau de proteção: IP54','Dimensões: 60 × 50 × 25 cm','Adesivo frontal incluso','Seletora de 3 posições: manual / desligado / automático','Comando por botoeiras','Parada de emergência','LED vermelho: comando ligado','LED amarelo: sobrecarga'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Redução dos picos de corrente durante a partida','Proteção do motor e aumento da vida útil','Diagrama elétrico facilita a manutenção e a compreensão do circuito','Sistema de aterramento reforça a segurança da instalação','Cabos numerados por anilhas para identificação rápida','Ligação rápida por bornes','Montagem técnica organizada e de alto padrão','Operação segura e estável'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor termomagnético geral','Barramento de potência','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Canaleta de cabeamento','Trilho DIN','Quadro metálico','Sinaleiros luminosos','Botoeiras liga e desliga','Seletora de três posições','Botão de emergência','Componentes Altronic, Siemens, Sibratec e Lukma, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'220 V trifásico',
        'power_range'=>'Até 50 CV',
        'reference_code'=>'E.T_50CV_220V_BOT',
        'model'=>'Painel Estrela Triângulo',
        'price_cents'=>549600,
        'installments'=>3,
        'warranty_days'=>365,
        'seo_title'=>'Painel Estrela Triângulo 50CV 220V Botoeiras | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 50 CV 220 V com botoeiras, proteção IP54, operação manual e automática, sinaleiros e parada de emergência.',
        'source_slug'=>$pushButton50Slug,
    ]);
}

$pushButton25_220Slug = 'painel-estrela-triangulo-25cv-220v-botoeiras';
$findPushButton25_220 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findPushButton25_220->execute(['slug'=>$pushButton25_220Slug]);
if (!$findPushButton25_220->fetchColumn()) {
    $clonePushButton25_220 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,:model,:price_cents,:installments,stock_status,stock_quantity,lead_time,sales_channel,:warranty_days,47,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $clonePushButton25_220->execute([
        'name'=>'Painel Estrela Triângulo 25CV 220V Botoeiras',
        'slug'=>$pushButton25_220Slug,
        'summary'=>'Painel estrela-triângulo de 25 CV em 220 V com botoeiras, desenvolvido para a partida segura de compressores, bombas e motores trifásicos.',
        'description'=>'O Painel Estrela Triângulo 25CV 220V com botoeiras foi desenvolvido para a partida segura e eficiente de compressores, bombas e motores trifásicos em ambientes industriais. A partida estrela-triângulo reduz os picos de corrente, aumenta a proteção do motor e prolonga a vida útil do conjunto. É indicado para compressores industriais, betoneiras, fornos, esteiras, portos, aeroportos, estádios, canteiros de obras, eventos e outros equipamentos trifásicos. Não é recomendado para partidas sob carga inicial; para essas aplicações, consulte a engenharia. Os botões da porta e do quadro são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos utiliza flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual. A instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado.',
        'features'=>json_encode(['SKU: E.T_25CV_220V_BOT','Número de fases: trifásico','Ligação de motores: trifásica','Potência máxima: 25 CV','Tensão: 220 V','Frequência: 60 Hz','Faixa de corrente térmica: 20 A – 30 A','A corrente do relé térmico é dividida por dois devido à comutação dos contatores','Grau de proteção: IP54','Dimensões: 40 × 30 × 20 cm','Adesivo frontal incluso','Seletora de 3 posições: manual / desligado / automático','Comando por botoeiras','Parada de emergência','LED vermelho: comando ligado','LED amarelo: sobrecarga'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Redução dos picos de corrente durante a partida','Proteção do motor e aumento da vida útil','Diagrama elétrico facilita a manutenção e a compreensão do circuito','Sistema de aterramento reforça a segurança da instalação','Cabos numerados por anilhas para identificação rápida','Ligação rápida por bornes','Montagem técnica organizada e de alto padrão','Operação segura e estável'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor termomagnético geral','Barramento de potência','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Canaleta de cabeamento','Trilho DIN','Quadro metálico','Sinaleiros luminosos','Botoeiras liga e desliga','Seletora de três posições','Botão de emergência','Componentes Altronic, Siemens, Sibratec e Lukma, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'220 V trifásico',
        'power_range'=>'Até 25 CV',
        'reference_code'=>'E.T_25CV_220V_BOT',
        'model'=>'Painel Estrela Triângulo',
        'price_cents'=>249300,
        'installments'=>3,
        'warranty_days'=>365,
        'seo_title'=>'Painel Estrela Triângulo 25CV 220V Botoeiras | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 25 CV 220 V com botoeiras, proteção IP54, operação manual e automática, sinaleiros e parada de emergência.',
        'source_slug'=>$pushButton50_220Slug,
    ]);
}

$pushButton20_220Slug = 'painel-estrela-triangulo-20cv-220v-botoeiras';
$findPushButton20_220 = $pdo->prepare('SELECT id FROM products WHERE slug=:slug AND deleted_at IS NULL LIMIT 1');
$findPushButton20_220->execute(['slug'=>$pushButton20_220Slug]);
if (!$findPushButton20_220->fetchColumn()) {
    $clonePushButton20_220 = $pdo->prepare("INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description) SELECT :name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,:reference_code,brand,:model,:price_cents,:installments,stock_status,stock_quantity,lead_time,sales_channel,:warranty_days,48,featured,'published',CURRENT_TIMESTAMP,:seo_title,:seo_description FROM products WHERE slug=:source_slug AND deleted_at IS NULL LIMIT 1");
    $clonePushButton20_220->execute([
        'name'=>'Painel Estrela Triângulo 20CV 220V Botoeiras',
        'slug'=>$pushButton20_220Slug,
        'summary'=>'Painel estrela-triângulo de 20 CV em 220 V com botoeiras, desenvolvido para a partida segura de compressores, bombas e motores trifásicos.',
        'description'=>'O Painel Estrela Triângulo 20CV 220V com botoeiras foi desenvolvido para a partida segura e eficiente de compressores, bombas e motores trifásicos em ambientes industriais. A partida estrela-triângulo reduz os picos de corrente, aumenta a proteção do motor e prolonga a vida útil do conjunto. É indicado para compressores industriais, betoneiras, bombas, fornos, esteiras, portos, aeroportos, estádios, canteiros de obras, eventos e outros equipamentos trifásicos. Não é recomendado para partidas sob carga inicial; para essas aplicações, consulte a engenharia. Os botões da porta e do quadro são enviados desmontados para evitar avarias durante o transporte. O engate dos contatos utiliza flanges de encaixe rápido, permitindo montagem e remoção sem ferramentas, com simples aperto manual. A instalação e o manuseio devem ser realizados por profissionais qualificados ou com conhecimento técnico adequado.',
        'features'=>json_encode(['SKU: E.T_20CV_220V_BOT','Número de fases: trifásico','Ligação de motores: trifásica','Potência máxima: 20 CV','Tensão: 220 V','Frequência: 60 Hz','Faixa de corrente térmica: 20 A – 25 A','A corrente do relé térmico é dividida por dois devido à comutação dos contatores','Grau de proteção: IP54','Dimensões: 40 × 30 × 20 cm','Adesivo frontal incluso','Seletora de 3 posições: manual / desligado / automático','Comando por botoeiras','Parada de emergência','LED vermelho: comando ligado','LED amarelo: sobrecarga'], JSON_UNESCAPED_UNICODE),
        'benefits'=>json_encode(['Redução dos picos de corrente durante a partida','Proteção do motor e aumento da vida útil','Diagrama elétrico facilita a manutenção e a compreensão do circuito','Sistema de aterramento reforça a segurança da instalação','Cabos numerados por anilhas para identificação rápida','Ligação rápida por bornes','Montagem técnica organizada e de alto padrão','Operação segura e estável'], JSON_UNESCAPED_UNICODE),
        'components'=>json_encode(['Disjuntor termomagnético geral','Barramento de potência','Relé de falta de fase','Relé estrela-triângulo','Contatores de potência','Relé térmico de sobrecarga','Canaleta de cabeamento','Trilho DIN','Quadro metálico','Sinaleiros luminosos','Botoeiras liga e desliga','Seletora de três posições','Botão de emergência','Componentes Altronic, Siemens, Sibratec e Lukma, sujeitos à disponibilidade em estoque'], JSON_UNESCAPED_UNICODE),
        'voltages'=>'220 V trifásico',
        'power_range'=>'Até 20 CV',
        'reference_code'=>'E.T_20CV_220V_BOT',
        'model'=>'Painel Estrela Triângulo',
        'price_cents'=>226400,
        'installments'=>3,
        'warranty_days'=>365,
        'seo_title'=>'Painel Estrela Triângulo 20CV 220V Botoeiras | Painel de Comando',
        'seo_description'=>'Painel estrela-triângulo 20 CV 220 V com botoeiras, proteção IP54, operação manual e automática, sinaleiros e parada de emergência.',
        'source_slug'=>$pushButton25_220Slug,
    ]);
}

$pdo->exec("UPDATE products SET
    brand=CASE WHEN UPPER(TRIM(brand))='APR' THEN 'Painel de Comando' ELSE REPLACE(brand,'APR','Painel de Comando') END,
    name=TRIM(REPLACE(REPLACE(REPLACE(name,' WEG K',''),' Weg K',''),'APR','Painel de Comando')),
    summary=TRIM(REPLACE(REPLACE(REPLACE(REPLACE(summary,'APR Soluções Industriais','Painel de Comando'),' WEG K',''),' Weg K',''),'APR','Painel de Comando')),
    description=TRIM(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(description,'APR Soluções Industriais','Painel de Comando'),'Painel APR','Painel de Comando'),'painéis APR','painéis Painel de Comando'),' WEG K',''),' Weg K',''),'APR','Painel de Comando')),
    features=REPLACE(REPLACE(REPLACE(features,'WEG, ',''),' WEG K',''),' Weg K',''),
    benefits=REPLACE(REPLACE(REPLACE(REPLACE(benefits,'WEG e ',''),'WEG, ',''),' WEG K',''),' Weg K',''),
    components=REPLACE(REPLACE(REPLACE(REPLACE(components,'WEG e ',''),'WEG, ',''),' WEG K',''),' Weg K',''),
    reference_code=REPLACE(reference_code,'APR','PAINEL_COMANDO'),
    model=TRIM(REPLACE(REPLACE(REPLACE(model,' WEG K',''),' Weg K',''),'APR','Painel de Comando')),
    seo_title=TRIM(REPLACE(REPLACE(REPLACE(seo_title,' WEG K',''),' Weg K',''),'APR','Painel de Comando')),
    seo_description=TRIM(REPLACE(REPLACE(REPLACE(REPLACE(seo_description,'WEG e ',''),'WEG, ',''),' WEG K',''),'APR','Painel de Comando')),
    updated_at=CURRENT_TIMESTAMP
WHERE deleted_at IS NULL");

require __DIR__ . '/upsert-soft-starter-30cv-380v.php';
require __DIR__ . '/upsert-soft-starter-45a-30cv-380v.php';
require __DIR__ . '/upsert-soft-starter-61a-40cv-380v.php';
require __DIR__ . '/upsert-soft-starter-30a-20cv-380v.php';
require __DIR__ . '/upsert-irrigation-soft-starter-125cv-220v.php';
require __DIR__ . '/upsert-irrigation-soft-starter-60cv-220v.php';
require __DIR__ . '/upsert-irrigation-soft-starter-50cv-220v.php';
require __DIR__ . '/upsert-soft-starter-85a-30cv-220v.php';
require __DIR__ . '/upsert-soft-starter-61a-20cv-220v.php';
require __DIR__ . '/upsert-soft-starter-45a-15cv-220v.php';
require __DIR__ . '/upsert-soft-starter-30a-10cv-220v.php';

echo "Banco local preparado.\nAdmin: {$email}\n";
