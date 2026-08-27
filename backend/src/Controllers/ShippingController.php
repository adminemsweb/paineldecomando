<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\ServiceUnavailableException;
use App\Services\CepService;
use App\Services\CorreiosService;
use RuntimeException;

final class ShippingController
{
    public function __construct(
        private readonly CorreiosService $correios,
        private readonly CepService $cepService = new CepService(),
    ) {}

    public function lookupCep(Request $request): never
    {
        $cep = (string)($request->body()['cep'] ?? '');
        try {
            Response::success($this->cepService->lookup($cep), 'CEP encontrado.');
        } catch (RuntimeException $exception) {
            Logger::warning('CEP lookup rejected', [
                'reason' => $exception->getMessage(),
                'cep_suffix' => substr(preg_replace('/\D/', '', $cep) ?? '', -3),
            ]);
            Response::error($exception->getMessage(), $exception instanceof ServiceUnavailableException ? 503 : 422);
        }
    }

    public function quote(Request $request): never
    {
        $cep = (string)($request->body()['cep'] ?? '');
        try {
            Response::success($this->correios->quote($cep), 'Frete calculado pelos Correios.');
        } catch (RuntimeException $exception) {
            $configurationError = str_contains($exception->getMessage(), 'aguardando credenciais') || $exception instanceof ServiceUnavailableException;
            Logger::warning('Shipping quote rejected', [
                'reason' => $exception->getMessage(),
                'cep_suffix' => substr(preg_replace('/\D/', '', $cep) ?? '', -3),
            ]);
            Response::error($exception->getMessage(), $configurationError ? 503 : 422);
        }
    }
}
