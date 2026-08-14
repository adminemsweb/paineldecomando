<?php
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class CepService
{
    private const API_BASE = 'https://viacep.com.br/ws';

    /** @return array{cep:string,address:string,city:string,uf:string,options:array{}} */
    public function lookup(string $value): array
    {
        $cep = preg_replace('/\D/', '', $value) ?? '';
        if (strlen($cep) !== 8) throw new RuntimeException('Informe um CEP válido com 8 dígitos.');

        $handle = curl_init(self::API_BASE . "/{$cep}/json/");
        if ($handle === false) throw new RuntimeException('Não foi possível iniciar a consulta do CEP.');

        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'User-Agent: PainelDeComando/1.0'],
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT => 8,
        ]);

        $raw = curl_exec($handle);
        $status = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        if (!is_string($raw) || $status < 200 || $status >= 300) {
            throw new RuntimeException('Não foi possível consultar esse CEP agora. Tente novamente.');
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || ($data['erro'] ?? false)) throw new RuntimeException('CEP não encontrado.');

        $street = trim((string)($data['logradouro'] ?? ''));
        $district = trim((string)($data['bairro'] ?? ''));
        $city = trim((string)($data['localidade'] ?? ''));
        $uf = trim((string)($data['uf'] ?? ''));
        if ($city === '' || $uf === '') throw new RuntimeException('O serviço de CEP retornou um endereço incompleto.');

        return [
            'cep' => $cep,
            'address' => implode(', ', array_filter([$street, $district, "{$city}/{$uf}"])),
            'city' => $city,
            'uf' => $uf,
            'options' => [],
        ];
    }
}
