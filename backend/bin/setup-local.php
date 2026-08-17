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

$pdo->exec("INSERT OR IGNORE INTO roles (name,slug,status) VALUES ('Superadministrador','superadmin','active'),('Administrador','admin','active'),('Cliente','customer','active')");

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

echo "Banco local preparado.\nAdmin: {$email}\n";
