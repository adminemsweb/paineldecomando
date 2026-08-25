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

exit($failures === [] ? 0 : 1);
