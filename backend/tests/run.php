<?php
declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $file = dirname(__DIR__) . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) require $file;
});

use App\Core\Logger;
use App\Services\CorreiosService;
use App\Validators\LeadValidator;

$failures = [];
$test = static function (string $name, callable $callback) use (&$failures): void {
    try { $callback(); echo "PASS {$name}\n"; }
    catch (Throwable $exception) { $failures[] = $name; echo "FAIL {$name}: {$exception->getMessage()}\n"; }
};
$assert = static function (bool $condition, string $message = 'Assertion failed'): void {
    if (!$condition) throw new RuntimeException($message);
};

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

$test('Logger grava JSON e remove segredos', static function () use ($assert): void {
    Logger::info('backend-test', ['token'=>'segredo','safe'=>'ok']);
    $path = dirname(__DIR__) . '/storage/logs/app-' . date('Y-m-d') . '.jsonl';
    $line = trim((string)array_slice(file($path, FILE_IGNORE_NEW_LINES) ?: [], -1)[0]);
    $record = json_decode($line, true, flags: JSON_THROW_ON_ERROR);
    $assert(($record['context']['token'] ?? null) === '[REDACTED]');
    $assert(($record['context']['safe'] ?? null) === 'ok');
});

exit($failures === [] ? 0 : 1);
