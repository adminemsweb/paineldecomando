<?php
declare(strict_types=1);

namespace App\Validators;

final class LeadValidator
{
    /** @param array<string,mixed> $data @return array<string,list<string>> */
    public static function validate(array $data): array
    {
        $errors = [];
        foreach (['name', 'email', 'phone', 'description'] as $field) if (trim((string)($data[$field] ?? '')) === '') $errors[$field][] = 'Campo obrigatório.';
        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) $errors['email'][] = 'Informe um e-mail válido.';
        if (!filter_var($data['consent'] ?? false, FILTER_VALIDATE_BOOL)) $errors['consent'][] = 'O consentimento é obrigatório.';
        return $errors;
    }
}

