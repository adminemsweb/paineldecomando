<?php
declare(strict_types=1);

namespace App\Validators;

use DateTimeImmutable;

final class LeadValidator
{
    /** @param array<string,mixed> $data @return array<string,list<string>> */
    public static function validate(array $data): array
    {
        $errors = [];
        foreach (['name', 'email', 'phone', 'description'] as $field) {
            if (!is_string($data[$field] ?? null) || trim($data[$field]) === '') $errors[$field][] = 'Campo obrigatório.';
        }
        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) $errors['email'][] = 'Informe um e-mail válido.';
        if (!filter_var($data['consent'] ?? false, FILTER_VALIDATE_BOOL)) $errors['consent'][] = 'O consentimento é obrigatório.';

        $limits = ['name'=>150,'company'=>190,'cnpj'=>18,'email'=>190,'phone'=>30,'whatsapp'=>30,'city'=>120,'state'=>2,'voltage'=>80,'power'=>100,'description'=>5000];
        foreach ($limits as $field => $maximum) {
            if (!array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') continue;
            if (!is_string($data[$field])) { $errors[$field][] = 'Formato inválido.'; continue; }
            if (self::length($data[$field]) > $maximum) $errors[$field][] = "Use no máximo {$maximum} caracteres.";
        }
        if (isset($data['state']) && $data['state'] !== '' && (!is_string($data['state']) || !preg_match('/^[A-Za-z]{2}$/', $data['state']))) $errors['state'][] = 'Informe uma UF válida.';
        if (isset($data['quantity']) && filter_var($data['quantity'], FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>1000]]) === false) $errors['quantity'][] = 'Informe uma quantidade entre 1 e 1000.';
        foreach (['segment_id','product_id'] as $field) {
            if (isset($data[$field]) && $data[$field] !== '' && filter_var($data[$field], FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) === false) $errors[$field][] = 'Informe um identificador válido.';
        }
        if (isset($data['desired_deadline']) && $data['desired_deadline'] !== '' && (!is_string($data['desired_deadline']) || !self::validDate($data['desired_deadline']))) $errors['desired_deadline'][] = 'Informe uma data válida no formato AAAA-MM-DD.';
        return $errors;
    }

    private static function length(string $value): int
    {
        if (function_exists('mb_strlen')) return mb_strlen($value, 'UTF-8');
        $count = preg_match_all('/./us', $value, $matches);
        return $count === false ? strlen($value) : $count;
    }

    private static function validDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
