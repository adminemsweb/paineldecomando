<?php
declare(strict_types=1);

namespace App\Services;

use App\Config\Env;
use RuntimeException;

final class CorreiosService
{
    private const API_BASE = 'https://api.correios.com.br';
    private static ?string $token = null;

    /** @return array{cep:string,address:string,city:string,uf:string,options:list<array{service:string,code:string,price:string,days:int}>} */
    public function quote(string $destinationCep): array
    {
        $cep = preg_replace('/\D/', '', $destinationCep) ?? '';
        if (strlen($cep) !== 8) throw new RuntimeException('Informe um CEP válido com 8 dígitos.');

        $this->assertConfigured();
        $token = $this->token();
        $addressData = $this->request('GET', '/cep/v2/enderecos/' . $cep, $token);
        $options = [];

        foreach ($this->serviceCodes() as $code => $service) {
            $query = http_build_query([
                'cepDestino' => $cep,
                'cepOrigem' => Env::get('SHIPPING_ORIGIN_CEP'),
                'psObjeto' => Env::get('SHIPPING_WEIGHT_GRAMS'),
                'tpObjeto' => '2',
                'comprimento' => Env::get('SHIPPING_LENGTH_CM'),
                'largura' => Env::get('SHIPPING_WIDTH_CM'),
                'altura' => Env::get('SHIPPING_HEIGHT_CM'),
            ]);
            $price = $this->request('GET', "/preco/v1/nacional/{$code}?{$query}", $token);
            $deadline = $this->request('GET', "/prazo/v1/nacional/{$code}?" . http_build_query([
                'cepOrigem' => Env::get('SHIPPING_ORIGIN_CEP'),
                'cepDestino' => $cep,
            ]), $token);

            if (!isset($price['pcFinal'])) continue;
            $options[] = [
                'service' => $service,
                'code' => $code,
                'price' => (string)$price['pcFinal'],
                'days' => (int)($deadline['prazoEntrega'] ?? 0),
            ];
        }

        $street = trim((string)($addressData['logradouro'] ?? ''));
        $district = trim((string)($addressData['bairro'] ?? ''));
        $city = trim((string)($addressData['localidade'] ?? ''));
        $uf = trim((string)($addressData['uf'] ?? ''));
        $address = implode(', ', array_filter([$street, $district, trim("{$city}/{$uf}", '/')])) ?: "CEP {$cep}";

        return ['cep' => $cep, 'address' => $address, 'city' => $city, 'uf' => $uf, 'options' => $options];
    }

    private function assertConfigured(): void
    {
        foreach (['CORREIOS_USER', 'CORREIOS_API_CODE', 'CORREIOS_CONTRACT', 'CORREIOS_POSTING_CARD', 'SHIPPING_ORIGIN_CEP', 'SHIPPING_WEIGHT_GRAMS', 'SHIPPING_LENGTH_CM', 'SHIPPING_WIDTH_CM', 'SHIPPING_HEIGHT_CM'] as $key) {
            if (!Env::get($key)) throw new RuntimeException('Integração dos Correios aguardando credenciais e dados da embalagem.');
        }
    }

    private function token(): string
    {
        if (self::$token !== null) return self::$token;
        $body = [
            'numero' => Env::get('CORREIOS_POSTING_CARD'),
            'contrato' => Env::get('CORREIOS_CONTRACT'),
        ];
        if (Env::get('CORREIOS_DR')) $body['dr'] = (int)Env::get('CORREIOS_DR');

        $response = $this->request('POST', '/token/v1/autentica/cartaopostagem', null, $body, true);
        $token = $response['token'] ?? null;
        if (!is_string($token) || $token === '') throw new RuntimeException('Os Correios não retornaram um token válido.');
        return self::$token = $token;
    }

    /** @return array<string,mixed> */
    private function request(string $method, string $path, ?string $token = null, ?array $body = null, bool $basic = false): array
    {
        $handle = curl_init(self::API_BASE . $path);
        if ($handle === false) throw new RuntimeException('Não foi possível iniciar a conexão com os Correios.');

        $headers = ['Accept: application/json'];
        if ($token) $headers[] = 'Authorization: Bearer ' . $token;
        if ($body !== null) $headers[] = 'Content-Type: application/json';

        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 12,
        ]);
        if ($basic) curl_setopt($handle, CURLOPT_USERPWD, Env::get('CORREIOS_USER') . ':' . Env::get('CORREIOS_API_CODE'));
        if ($body !== null) curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($body, JSON_THROW_ON_ERROR));

        $raw = curl_exec($handle);
        $status = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $error = curl_error($handle);
        curl_close($handle);
        if (!is_string($raw)) throw new RuntimeException('Falha ao consultar os Correios: ' . $error);

        $decoded = json_decode($raw, true);
        if ($status < 200 || $status >= 300) {
            $message = is_array($decoded) ? ($decoded['msgs'][0] ?? $decoded['mensagem'] ?? $decoded['message'] ?? null) : null;
            throw new RuntimeException(is_string($message) ? $message : 'Os Correios não conseguiram calcular o frete neste momento.');
        }
        if (!is_array($decoded)) throw new RuntimeException('Resposta inválida recebida dos Correios.');
        return $decoded;
    }

    /** @return array<string,string> */
    private function serviceCodes(): array
    {
        $codes = array_filter(array_map('trim', explode(',', Env::get('CORREIOS_SERVICE_CODES', '03298,03220') ?? '')));
        $names = ['03298' => 'PAC', '03220' => 'SEDEX'];
        $result = [];
        foreach ($codes as $code) $result[$code] = $names[$code] ?? "Correios {$code}";
        return $result;
    }
}
