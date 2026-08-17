<?php
declare(strict_types=1);

namespace App\Validators;

final class ProductValidator
{
    /** @param array<string,mixed> $data @return array<string,list<string>> */
    public static function validate(array $data): array
    {
        $errors = [];
        $name = trim((string)($data['name'] ?? ''));
        $slug = trim((string)($data['slug'] ?? ''));
        if (mb_strlen($name) < 3 || mb_strlen($name) > 190) $errors['name'][] = 'Informe um nome entre 3 e 190 caracteres.';
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) || strlen($slug) > 210) $errors['slug'][] = 'Use apenas letras minúsculas, números e hífens.';
        if (!in_array($data['status'] ?? '', ['draft','published','archived'], true)) $errors['status'][] = 'Selecione um status válido.';
        if (isset($data['features']) && !is_array($data['features'])) $errors['features'][] = 'As características devem ser uma lista.';
        if (isset($data['benefits']) && !is_array($data['benefits'])) $errors['benefits'][] = 'Os benefícios devem ser uma lista.';
        if (isset($data['components']) && !is_array($data['components'])) $errors['components'][] = 'Os componentes devem ser uma lista.';
        if (isset($data['gallery_images']) && !is_array($data['gallery_images'])) $errors['gallery_images'][] = 'As fotos devem ser uma lista.';
        if (is_array($data['gallery_images'] ?? null) && count($data['gallery_images']) > 4) $errors['gallery_images'][] = 'Você pode adicionar no máximo 5 fotos contando a principal.';
        if (isset($data['video_urls']) && !is_array($data['video_urls'])) $errors['video_urls'][] = 'Os vídeos devem ser uma lista.';
        if (is_array($data['video_urls'] ?? null) && count($data['video_urls']) > 2) $errors['video_urls'][] = 'Você pode adicionar no máximo 2 vídeos.';
        if (!in_array($data['sales_channel'] ?? 'both', ['site','whatsapp','both'], true)) $errors['sales_channel'][] = 'Selecione um canal de venda válido.';
        if (!in_array($data['stock_status'] ?? 'on_demand', ['in_stock','out_of_stock','on_demand'], true)) $errors['stock_status'][] = 'Selecione uma situação de estoque válida.';
        if (isset($data['price_cents']) && $data['price_cents'] !== '' && (int)$data['price_cents'] < 0) $errors['price_cents'][] = 'O preço não pode ser negativo.';
        if ((int)($data['installments'] ?? 1) < 1 || (int)($data['installments'] ?? 1) > 24) $errors['installments'][] = 'Informe entre 1 e 24 parcelas.';
        if ((int)($data['stock_quantity'] ?? 0) < 0) $errors['stock_quantity'][] = 'O estoque não pode ser negativo.';
        if ((int)($data['sort_order'] ?? 0) < 0) $errors['sort_order'][] = 'A ordem não pode ser negativa.';
        return $errors;
    }
}
