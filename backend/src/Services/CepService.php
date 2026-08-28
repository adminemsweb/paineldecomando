<?php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ServiceUnavailableException;
use App\Support\CurlTlsOptions;
use App\Support\ExternalRequestGuard;
use App\Support\FileCache;
use RuntimeException;

final class CepService
{
    private const BRASIL_API_BASE = 'https://brasilapi.com.br/api/cep/v1';
    private const VIA_CEP_BASE = 'https://viacep.com.br/ws';

    /** @return array{cep:string,address:string,street:string,district:string,city:string,uf:string,options:array{}} */
    public function lookup(string $value): array
    {
        $cep = preg_replace('/\D/', '', $value) ?? '';
        if (strlen($cep) !== 8) throw new RuntimeException('Informe um CEP válido com 8 dígitos.');

        return FileCache::remember('cep', $cep, 86400, fn(): array => ExternalRequestGuard::run(fn(): array => $this->lookupProviders($cep)));
    }

    /** @return array{cep:string,address:string,street:string,district:string,city:string,uf:string,options:array{}} */
    private function lookupProviders(string $cep): array
    {
        $providers = [
            fn(): ?array => $this->fromBrasilApi($cep),
            fn(): ?array => $this->fromViaCep($cep),
        ];
        $providerFailures = 0;
        $data = null;
        foreach ($providers as $provider) {
            try {
                $data = $provider();
                if ($data !== null) break;
            } catch (RuntimeException) {
                $providerFailures++;
            }
        }

        if ($data === null && $providerFailures === count($providers)) throw new ServiceUnavailableException('Não foi possível consultar esse CEP agora. Tente novamente.');
        if ($data === null) throw new RuntimeException('CEP não encontrado.');

        $street = trim((string)($data['street'] ?? ''));
        $district = trim((string)($data['district'] ?? ''));
        $city = trim((string)($data['city'] ?? ''));
        $uf = trim((string)($data['uf'] ?? ''));
        if ($city === '' || $uf === '') throw new RuntimeException('O serviço de CEP retornou um endereço incompleto.');

        return [
            'cep'=>$cep,
            'address'=>implode(', ', array_filter([$street, $district, "{$city}/{$uf}"])),
            'street'=>$street,
            'district'=>$district,
            'city'=>$city,
            'uf'=>$uf,
            'options'=>[],
        ];
    }

    /** @return array{street:string,district:string,city:string,uf:string}|null */
    private function fromBrasilApi(string $cep): ?array
    {
        [$status, $data] = $this->request(self::BRASIL_API_BASE . "/{$cep}");
        if ($status === 404) return null;
        if ($status < 200 || $status >= 300 || !is_array($data)) throw new RuntimeException('BrasilAPI indisponível.');
        return ['street'=>(string)($data['street'] ?? ''),'district'=>(string)($data['neighborhood'] ?? ''),'city'=>(string)($data['city'] ?? ''),'uf'=>(string)($data['state'] ?? '')];
    }

    /** @return array{street:string,district:string,city:string,uf:string}|null */
    private function fromViaCep(string $cep): ?array
    {
        [$status, $data] = $this->request(self::VIA_CEP_BASE . "/{$cep}/json/");
        if ($status < 200 || $status >= 300 || !is_array($data)) throw new RuntimeException('ViaCEP indisponível.');
        if (($data['erro'] ?? false) === true) return null;
        return ['street'=>(string)($data['logradouro'] ?? ''),'district'=>(string)($data['bairro'] ?? ''),'city'=>(string)($data['localidade'] ?? ''),'uf'=>(string)($data['uf'] ?? '')];
    }

    /** @return array{0:int,1:mixed} */
    private function request(string $url): array
    {
        $handle = curl_init($url);
        if ($handle === false) throw new RuntimeException('Não foi possível iniciar a consulta do CEP.');
        $options = [CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPHEADER=>['Accept: application/json','User-Agent: PainelDeComando/1.0'],CURLOPT_CONNECTTIMEOUT=>3,CURLOPT_TIMEOUT=>6];
        $options += CurlTlsOptions::trustedCertificateOptions();
        curl_setopt_array($handle, $options);
        $raw = curl_exec($handle);
        $status = (int)curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);
        if (!is_string($raw)) throw new RuntimeException('O provedor de CEP não respondeu.');
        return [$status, json_decode($raw, true)];
    }
}
