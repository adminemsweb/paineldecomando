<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Services\CorreiosService;
use RuntimeException;

final class ShippingController
{
    public function __construct(private readonly CorreiosService $correios) {}

    public function quote(Request $request): never
    {
        $cep = (string)($request->body()['cep'] ?? '');
        try {
            Response::success($this->correios->quote($cep), 'Frete calculado pelos Correios.');
        } catch (RuntimeException $exception) {
            $configurationError = str_contains($exception->getMessage(), 'aguardando credenciais');
            Logger::warning('Shipping quote rejected', [
                'reason' => $exception->getMessage(),
                'cep_suffix' => substr(preg_replace('/\D/', '', $cep) ?? '', -3),
            ]);
            Response::error($exception->getMessage(), $configurationError ? 503 : 422);
        }
    }
}
