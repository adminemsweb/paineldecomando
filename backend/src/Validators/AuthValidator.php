<?php
declare(strict_types=1);

namespace App\Validators;

final class AuthValidator
{
    /** @param array<string,mixed> $data @return array<string,list<string>> */
    public static function registration(array $data): array
    {
        $errors = self::login($data);
        $name = trim((string)($data['name'] ?? ''));
        $phone = preg_replace('/\D/', '', (string)($data['phone'] ?? '')) ?? '';
        $password = (string)($data['password'] ?? '');
        if (strlen($name) < 2 || strlen($name) > 150) $errors['name'][] = 'Informe seu nome completo.';
        if (!in_array(strlen($phone), [10, 11], true)) $errors['phone'][] = 'Informe um telefone com DDD.';
        if (strlen((string)($data['company'] ?? '')) > 190) $errors['company'][] = 'O nome da empresa é muito longo.';
        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            $errors['password'] = ['Use pelo menos 8 caracteres, com letras e números.'];
        }
        if (($data['consent'] ?? false) !== true) $errors['consent'][] = 'Aceite a Política de Privacidade para continuar.';
        return $errors;
    }

    /** @param array<string,mixed> $data @return array<string,list<string>> */
    public static function login(array $data): array
    {
        $errors = [];
        $email = strtolower(trim((string)($data['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) $errors['email'][] = 'Informe um e-mail válido.';
        if ((string)($data['password'] ?? '') === '') $errors['password'][] = 'Informe sua senha.';
        return $errors;
    }

    /** @param array<string,mixed> $data @return array<string,list<string>> */
    public static function forgotPassword(array $data): array
    {
        $email = strtolower(trim((string)($data['email'] ?? '')));
        return !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190 ? ['email'=>['Informe um e-mail válido.']] : [];
    }

    /** @param array<string,mixed> $data @return array<string,list<string>> */
    public static function resetPassword(array $data): array
    {
        $errors = [];
        $token = (string)($data['token'] ?? '');
        $password = (string)($data['password'] ?? '');
        if (strlen($token) !== 64 || !ctype_xdigit($token)) $errors['token'][] = 'O link de recuperação é inválido.';
        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) $errors['password'][] = 'Use pelo menos 8 caracteres, com letras e números.';
        if ($password !== (string)($data['password_confirmation'] ?? '')) $errors['password_confirmation'][] = 'As senhas não coincidem.';
        return $errors;
    }

    /** @param array<string,mixed> $data @return array<string,list<string>> */
    public static function profile(array $data): array
    {
        $errors = [];
        $name = trim((string)($data['name'] ?? ''));
        $phone = preg_replace('/\D/', '', (string)($data['phone'] ?? '')) ?? '';
        if (strlen($name) < 2 || strlen($name) > 150) $errors['name'][] = 'Informe seu nome completo.';
        if (!in_array(strlen($phone), [10, 11], true)) $errors['phone'][] = 'Informe um telefone com DDD.';
        if (strlen((string)($data['company'] ?? '')) > 190) $errors['company'][] = 'O nome da empresa é muito longo.';
        if (!array_key_exists('address', $data)) return $errors;
        $address = is_array($data['address'] ?? null) ? $data['address'] : [];
        $postalCode = preg_replace('/\D/', '', (string)($address['postalCode'] ?? '')) ?? '';
        if (strlen($postalCode) !== 8) $errors['postalCode'][] = 'Informe um CEP válido.';
        foreach (['street'=>'rua','number'=>'número','district'=>'bairro','city'=>'cidade'] as $field => $label) {
            if (trim((string)($address[$field] ?? '')) === '') $errors[$field][] = "Informe {$label}.";
        }
        if (!preg_match('/^[A-Z]{2}$/', strtoupper(trim((string)($address['state'] ?? ''))))) $errors['state'][] = 'Informe a UF com 2 letras.';
        if (strlen((string)($address['complement'] ?? '')) > 120) $errors['complement'][] = 'O complemento é muito longo.';
        return $errors;
    }
}
