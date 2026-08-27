<?php
declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $file = dirname(__DIR__) . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) require $file;
});

use App\Core\Logger;
use App\Config\Env;
use App\Middleware\RateLimiter;
use App\Services\CepService;
use App\Services\CorreiosService;
use App\Repositories\AuthRepository;
use App\Repositories\CategoryRepository;
use App\Services\AuthService;
use App\Validators\AuthValidator;
use App\Validators\LeadValidator;

$failures = [];
$test = static function (string $name, callable $callback) use (&$failures): void {
    try { $callback(); echo "PASS {$name}\n"; }
    catch (Throwable $exception) { $failures[] = $name; echo "FAIL {$name}: {$exception->getMessage()}\n"; }
};
$assert = static function (bool $condition, string $message = 'Assertion failed'): void {
    if (!$condition) throw new RuntimeException($message);
};

$test('Env carrega segredos montados em arquivo', static function () use ($assert): void {
    $path = tempnam(sys_get_temp_dir(), 'painel-secret-');
    if ($path === false) throw new RuntimeException('Nao foi possivel criar o segredo temporario.');
    file_put_contents($path, "senha-segura\n");
    $_ENV['TEST_SECRET_FILE'] = $path;
    try {
        $assert(Env::get('TEST_SECRET') === 'senha-segura', 'O segredo em arquivo nao foi carregado.');
    } finally {
        unset($_ENV['TEST_SECRET_FILE']);
        unlink($path);
    }
});

$test('LeadValidator rejeita campos obrigatórios ausentes', static function () use ($assert): void {
    $errors = LeadValidator::validate([]);
    $assert(isset($errors['name'], $errors['email'], $errors['phone'], $errors['description'], $errors['consent']));
});

$test('LeadValidator aceita payload válido', static function () use ($assert): void {
    $errors = LeadValidator::validate(['name'=>'Maria','email'=>'maria@example.com','phone'=>'11999999999','description'=>'Painel 220 V','consent'=>true]);
    $assert($errors === [], 'Payload válido retornou erros.');
});

$test('CorreiosService valida CEP antes de acessar credenciais', static function (): void {
    try { (new CorreiosService())->quote('123'); }
    catch (RuntimeException $exception) {
        if (str_contains($exception->getMessage(), 'CEP válido')) return;
        throw $exception;
    }
    throw new RuntimeException('CEP inválido foi aceito.');
});

$test('CepService valida CEP antes de consultar o provedor', static function (): void {
    try { (new CepService())->lookup('123'); }
    catch (RuntimeException $exception) {
        if (str_contains($exception->getMessage(), 'CEP v')) return;
        throw $exception;
    }
    throw new RuntimeException('CEP invalido foi aceito.');
});

$test('CategoryRepository mantém categorias e subcategorias em hierarquia', static function () use ($assert): void {
    $pdo = new PDO('sqlite::memory:', options:[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    $pdo->exec("CREATE TABLE categories (id INTEGER PRIMARY KEY AUTOINCREMENT,parent_id INTEGER NULL,name TEXT NOT NULL,slug TEXT NOT NULL,description TEXT NULL,status TEXT NOT NULL,sort_order INTEGER NOT NULL DEFAULT 0,seo_title TEXT NULL,seo_description TEXT NULL,created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,deleted_at TEXT NULL)");
    $pdo->exec("INSERT INTO categories (id,parent_id,name,slug,status,sort_order) VALUES (1,NULL,'Painéis de partida','paineis-de-partida','published',1),(2,1,'Estrela-Triângulo Econômico','estrela-triangulo-economico','published',1),(3,NULL,'Automação','automacao','draft',2)");
    $categories = (new CategoryRepository($pdo))->all();
    $assert(count($categories) === 3, 'Quantidade de categorias incorreta.');
    $assert($categories[0]['name'] === 'Painéis de partida' && $categories[1]['parent_id'] === 1, 'A subcategoria não ficou abaixo da categoria pai.');
    $assert($categories[2]['status'] === 'draft', 'O status administrativo não foi preservado.');
});

$test('AuthValidator exige senha forte, telefone e consentimento', static function () use ($assert): void {
    $errors = AuthValidator::registration(['name'=>'A','email'=>'invalido','phone'=>'123','password'=>'abc','consent'=>false]);
    $assert(isset($errors['name'], $errors['email'], $errors['phone'], $errors['password'], $errors['consent']));
});

$test('AuthService cadastra cliente com senha e sessao protegidas', static function () use ($assert): void {
    $pdo = new PDO('sqlite::memory:', options:[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    $pdo->exec("CREATE TABLE roles (id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL,slug TEXT NOT NULL UNIQUE,status TEXT NOT NULL); CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT NOT NULL,email TEXT NOT NULL UNIQUE,password_hash TEXT NOT NULL,status TEXT NOT NULL,failed_login_attempts INTEGER NOT NULL DEFAULT 0,locked_until TEXT NULL,last_login_at TEXT NULL,created_at TEXT NOT NULL,updated_at TEXT NOT NULL,deleted_at TEXT NULL); CREATE TABLE user_roles (user_id INTEGER NOT NULL,role_id INTEGER NOT NULL,created_at TEXT NOT NULL,PRIMARY KEY(user_id,role_id)); CREATE TABLE customer_profiles (user_id INTEGER PRIMARY KEY,company_name TEXT NULL,phone TEXT NOT NULL,lgpd_consent_at TEXT NOT NULL,created_at TEXT NOT NULL,updated_at TEXT NOT NULL); CREATE TABLE user_sessions (id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER NOT NULL,token_hash TEXT NOT NULL UNIQUE,expires_at TEXT NOT NULL,last_used_at TEXT NOT NULL,user_agent TEXT NULL,created_at TEXT NOT NULL,revoked_at TEXT NULL); CREATE TABLE customer_addresses (id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER NOT NULL,label TEXT NOT NULL,recipient_name TEXT NOT NULL,postal_code TEXT NOT NULL,street TEXT NOT NULL,number TEXT NOT NULL,complement TEXT NULL,district TEXT NOT NULL,city TEXT NOT NULL,state TEXT NOT NULL,is_default INTEGER NOT NULL,created_at TEXT NOT NULL,updated_at TEXT NOT NULL)");
    $pdo->exec("INSERT INTO roles (name,slug,status) VALUES ('Cliente','customer','active')");
    $service = new AuthService(new AuthRepository($pdo));
    $result = $service->register(['name'=>'Maria Cliente','email'=>'MARIA@example.com','company'=>'Indústria Teste','phone'=>'(11) 99999-9999','password'=>'Senha123','consent'=>true], 'Teste');
    $stored = $pdo->query("SELECT password_hash FROM users WHERE email='maria@example.com'")->fetchColumn();
    $sessionHash = $pdo->query('SELECT token_hash FROM user_sessions')->fetchColumn();
    $assert(is_string($stored) && $stored !== 'Senha123' && password_verify('Senha123', $stored), 'Senha não foi protegida corretamente.');
    $assert(is_string($sessionHash) && $sessionHash === hash('sha256', $result['token']) && $sessionHash !== $result['token'], 'Token de sessão não foi protegido.');
    $assert($service->currentUser($result['token'])['email'] === 'maria@example.com');
    $updated = $service->updateProfile($result['token'], ['name'=>'Maria Atualizada','company'=>'Nova Empresa','phone'=>'11988887777','address'=>['postalCode'=>'18056340','street'=>'Rua Teste','number'=>'123','complement'=>'Sala 2','district'=>'Centro','city'=>'Sorocaba','state'=>'SP']]);
    $assert($updated['name'] === 'Maria Atualizada' && $updated['address']['number'] === '123', 'Perfil e endereço não foram atualizados.');
    $personalOnly = $service->updateProfile($result['token'], ['name'=>'Maria Atualizada','company'=>'Nova Empresa','phone'=>'11977776666']);
    $assert($personalOnly['phone'] === '11977776666' && $personalOnly['address']['number'] === '123', 'Atualização dos dados pessoais alterou o endereço.');
    $service->logout($result['token']);
    $loggedOut = false;
    try { $service->currentUser($result['token']); }
    catch (Throwable) { $loggedOut = true; }
    $assert($loggedOut, 'Sessão continuou ativa após logout.');
    $login = $service->login(['email'=>'maria@example.com','password'=>'Senha123'], 'Teste');
    $assert($login['user']['name'] === 'Maria Atualizada', 'Login não retornou os dados atualizados do cliente.');
});

$test('Logger grava JSON e remove segredos', static function () use ($assert): void {
    Logger::info('backend-test', ['token'=>'segredo','password'=>'Senha123','authorization'=>'Bearer segredo','email'=>'maria@example.com','safe'=>'ok']);
    $path = dirname(__DIR__) . '/storage/logs/app-' . date('Y-m-d') . '.jsonl';
    $line = trim((string)array_slice(file($path, FILE_IGNORE_NEW_LINES) ?: [], -1)[0]);
    $record = json_decode($line, true, flags: JSON_THROW_ON_ERROR);
    $assert(($record['context']['token'] ?? null) === '[REDACTED]');
    $assert(($record['context']['password'] ?? null) === '[REDACTED]');
    $assert(($record['context']['authorization'] ?? null) === '[REDACTED]');
    $assert(($record['context']['email'] ?? null) === '[REDACTED]');
    $assert(($record['context']['safe'] ?? null) === 'ok');
});

$test('RateLimiter bloqueia excesso sem armazenar IP em claro', static function () use ($assert): void {
    $directory = sys_get_temp_dir() . '/painel-rate-limit-' . bin2hex(random_bytes(6));
    $_ENV['RATE_LIMIT_PATH'] = $directory;
    try {
        $first = RateLimiter::attempt('login-test', 1, 60, '203.0.113.42');
        $second = RateLimiter::attempt('login-test', 1, 60, '203.0.113.42');
        $assert($first['allowed'] && !$second['allowed'], 'O excesso de tentativas não foi bloqueado.');
        $assert($second['client_id'] !== '203.0.113.42' && strlen($second['client_id']) === 12, 'O IP não foi anonimizado.');
    } finally {
        foreach (glob($directory . '/*.json') ?: [] as $file) unlink($file);
        if (is_dir($directory)) rmdir($directory);
        unset($_ENV['RATE_LIMIT_PATH']);
    }
});

$test('LeadValidator limita tamanhos, tipos e faixas', static function () use ($assert): void {
    $errors = LeadValidator::validate(['name'=>str_repeat('a',151),'email'=>'maria@example.com','phone'=>['1199'],'description'=>'Painel','state'=>'Sao Paulo','quantity'=>1001,'desired_deadline'=>'2026-02-30','consent'=>true]);
    $assert(isset($errors['name'], $errors['phone'], $errors['state'], $errors['quantity'], $errors['desired_deadline']), 'Payload abusivo não foi rejeitado por completo.');
});

$test('LeadService reutiliza protocolo de submissão duplicada recente', static function () use ($assert): void {
    $pdo = new PDO('sqlite::memory:', options:[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    $pdo->sqliteCreateFunction('NOW', static fn(): string => date('Y-m-d H:i:s'));
    $pdo->exec('CREATE TABLE leads (id INTEGER PRIMARY KEY AUTOINCREMENT,protocol TEXT UNIQUE,name TEXT,company TEXT,cnpj TEXT,email TEXT,phone TEXT,whatsapp TEXT,city TEXT,state TEXT,segment_id INTEGER,product_id INTEGER,voltage TEXT,power TEXT,quantity INTEGER,desired_deadline TEXT,description TEXT,lgpd_consent INTEGER,status TEXT,source TEXT,created_at TEXT,updated_at TEXT,deleted_at TEXT)');
    $service = new \App\Services\LeadService($pdo);
    $payload = ['name'=>'Maria','email'=>'maria@example.com','phone'=>'11999999999','description'=>'Painel 220 V','consent'=>true];
    $first = $service->create($payload);
    $second = $service->create($payload);
    $assert($first['protocol'] === $second['protocol'], 'A duplicata criou outro protocolo.');
    $assert((int)$pdo->query('SELECT COUNT(*) FROM leads')->fetchColumn() === 1, 'A duplicata criou outra linha.');
});

$test('RequestContext confia em X-Forwarded-For apenas vindo de proxy autorizado', static function () use ($assert): void {
    $originalServer = $_SERVER;
    $_ENV['TRUSTED_PROXY_CIDRS'] = '10.0.0.0/8,172.16.0.0/12';
    try {
        $_SERVER['REMOTE_ADDR'] = '172.18.0.4';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.25, 10.0.0.8';
        $assert(\App\Core\RequestContext::clientIp() === '198.51.100.25', 'O IP original não foi resolvido pela cadeia confiável.');
        $_SERVER['REMOTE_ADDR'] = '203.0.113.9';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.99';
        $assert(\App\Core\RequestContext::clientIp() === '203.0.113.9', 'Um cliente direto conseguiu forjar X-Forwarded-For.');
    } finally {
        $_SERVER = $originalServer;
        unset($_ENV['TRUSTED_PROXY_CIDRS']);
    }
});

$test('RateLimiter separa visitantes resolvidos atrás do proxy', static function () use ($assert): void {
    $directory = sys_get_temp_dir() . '/painel-rate-limit-' . bin2hex(random_bytes(6));
    $originalServer = $_SERVER;
    $_ENV['RATE_LIMIT_PATH'] = $directory;
    $_ENV['TRUSTED_PROXY_CIDRS'] = '172.16.0.0/12';
    try {
        $_SERVER['REMOTE_ADDR'] = '172.18.0.4';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.1';
        $assert(RateLimiter::attempt('proxy-test', 1, 60)['allowed']);
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.2';
        $assert(RateLimiter::attempt('proxy-test', 1, 60)['allowed'], 'Visitantes diferentes compartilharam o mesmo bucket.');
    } finally {
        foreach (glob($directory . '/*.json') ?: [] as $file) unlink($file);
        if (is_dir($directory)) rmdir($directory);
        $_SERVER = $originalServer;
        unset($_ENV['RATE_LIMIT_PATH'], $_ENV['TRUSTED_PROXY_CIDRS']);
    }
});

$test('FileCache evita repetir trabalho e guard limita concorrência', static function () use ($assert): void {
    $root = sys_get_temp_dir() . '/painel-runtime-' . bin2hex(random_bytes(6));
    $_ENV['CACHE_PATH'] = $root . '/cache';
    $_ENV['EXTERNAL_GUARD_PATH'] = $root . '/guards';
    $_ENV['EXTERNAL_MAX_CONCURRENCY'] = '1';
    try {
        $calls = 0;
        $first = \App\Support\FileCache::remember('test', 'key', 60, static function () use (&$calls): array { $calls++; return ['ok'=>true]; });
        $second = \App\Support\FileCache::remember('test', 'key', 60, static function () use (&$calls): array { $calls++; return ['ok'=>false]; });
        $assert($first === $second && $calls === 1, 'O cache não reutilizou a resposta válida.');
        $blocked = false;
        \App\Support\ExternalRequestGuard::run(static function () use (&$blocked): void {
            try { \App\Support\ExternalRequestGuard::run(static fn() => null); }
            catch (\App\Exceptions\ServiceUnavailableException) { $blocked = true; }
        });
        $assert($blocked, 'O limite global de concorrência não bloqueou a segunda operação.');
    } finally {
        $iterator = is_dir($root) ? new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) : [];
        foreach ($iterator as $item) $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        if (is_dir($root)) rmdir($root);
        unset($_ENV['CACHE_PATH'], $_ENV['EXTERNAL_GUARD_PATH'], $_ENV['EXTERNAL_MAX_CONCURRENCY']);
    }
});

$test('RateLimiter falha fechado nos endpoints públicos protegidos', static function () use ($assert): void {
    $path = tempnam(sys_get_temp_dir(), 'painel-rate-file-');
    if ($path === false) throw new RuntimeException('Não foi possível criar o caminho de teste.');
    $_ENV['RATE_LIMIT_PATH'] = $path;
    try {
        $result = RateLimiter::attempt('public-test', 1, 60, '198.51.100.4', true);
        $assert(!$result['allowed'] && $result['retry_after'] > 0, 'A falha de armazenamento liberou um endpoint público.');
    } finally {
        unset($_ENV['RATE_LIMIT_PATH']);
        unlink($path);
    }
});

$test('RateLimiter global limita tentativas distribuídas', static function () use ($assert): void {
    $directory = sys_get_temp_dir() . '/painel-rate-global-' . bin2hex(random_bytes(6));
    $_ENV['RATE_LIMIT_PATH'] = $directory;
    try {
        $first = RateLimiter::attempt('auth-global-test', 1, 60, 'global', true);
        $second = RateLimiter::attempt('auth-global-test', 1, 60, 'global', true);
        $assert($first['allowed'] && !$second['allowed'], 'O teto global não bloqueou tentativas distribuídas.');
    } finally {
        foreach (glob($directory . '/*.json') ?: [] as $file) unlink($file);
        if (is_dir($directory)) rmdir($directory);
        unset($_ENV['RATE_LIMIT_PATH']);
    }
});

$test('Falhas repetidas não bloqueiam cliente nem administrador', static function () use ($assert): void {
    $pdo = new PDO('sqlite::memory:', options:[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    $pdo->exec("CREATE TABLE roles (id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT,slug TEXT UNIQUE,status TEXT); CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT,email TEXT UNIQUE,password_hash TEXT,status TEXT,failed_login_attempts INTEGER DEFAULT 0,locked_until TEXT,last_login_at TEXT,created_at TEXT,updated_at TEXT,deleted_at TEXT); CREATE TABLE user_roles (user_id INTEGER,role_id INTEGER,created_at TEXT,PRIMARY KEY(user_id,role_id)); CREATE TABLE user_sessions (id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER,token_hash TEXT UNIQUE,expires_at TEXT,last_used_at TEXT,user_agent TEXT,created_at TEXT,revoked_at TEXT); CREATE TABLE customer_profiles (user_id INTEGER PRIMARY KEY,company_name TEXT,phone TEXT,lgpd_consent_at TEXT,created_at TEXT,updated_at TEXT); CREATE TABLE customer_addresses (id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER,label TEXT,recipient_name TEXT,postal_code TEXT,street TEXT,number TEXT,complement TEXT,district TEXT,city TEXT,state TEXT,is_default INTEGER,created_at TEXT,updated_at TEXT)");
    $now = date('Y-m-d H:i:s');
    $pdo->exec("INSERT INTO roles (id,name,slug,status) VALUES (1,'Cliente','customer','active'),(2,'Admin','admin','active')");
    $insert = $pdo->prepare('INSERT INTO users (id,name,email,password_hash,status,failed_login_attempts,locked_until,created_at,updated_at) VALUES (:id,:name,:email,:hash,\'active\',0,NULL,:created,:updated)');
    $insert->execute(['id'=>1,'name'=>'Cliente','email'=>'cliente@example.com','hash'=>password_hash('Senha123', PASSWORD_DEFAULT),'created'=>$now,'updated'=>$now]);
    $insert->execute(['id'=>2,'name'=>'Admin','email'=>'admin@example.com','hash'=>password_hash('Admin123', PASSWORD_DEFAULT),'created'=>$now,'updated'=>$now]);
    $pdo->exec("INSERT INTO user_roles (user_id,role_id,created_at) VALUES (1,1,'{$now}'),(2,2,'{$now}'); INSERT INTO customer_profiles (user_id,company_name,phone,lgpd_consent_at,created_at,updated_at) VALUES (1,NULL,'11999999999','{$now}','{$now}','{$now}')");
    $service = new AuthService(new AuthRepository($pdo));
    for ($attempt = 0; $attempt < 6; $attempt++) { try { $service->login(['email'=>'cliente@example.com','password'=>'Errada']); } catch (\App\Exceptions\AuthException) {} }
    $assert($service->login(['email'=>'cliente@example.com','password'=>'Senha123'])['user']['email'] === 'cliente@example.com', 'O cliente permaneceu bloqueado.');
    for ($attempt = 0; $attempt < 6; $attempt++) { try { $service->login(['email'=>'admin@example.com','password'=>'Errada']); } catch (\App\Exceptions\AuthException) {} }
    $assert($service->adminLogin(['email'=>'admin@example.com','password'=>'Admin123'])['user']['role'] === 'admin', 'Falhas no login de cliente bloquearam o administrador.');
});

exit($failures === [] ? 0 : 1);
