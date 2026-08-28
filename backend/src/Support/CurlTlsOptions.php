<?php
declare(strict_types=1);

namespace App\Support;

final class CurlTlsOptions
{
    /** @return array<int,string> */
    public static function trustedCertificateOptions(): array
    {
        $configured = ini_get('curl.cainfo');
        $candidates = [
            getenv('CURL_CA_BUNDLE') ?: null,
            getenv('SSL_CERT_FILE') ?: null,
            is_string($configured) && $configured !== '' ? $configured : null,
        ];

        if (PHP_OS_FAMILY === 'Windows') {
            $candidates[] = 'C:\\Program Files\\Git\\mingw64\\etc\\ssl\\certs\\ca-bundle.crt';
            $candidates[] = 'C:\\Program Files\\Git\\usr\\ssl\\certs\\ca-bundle.crt';
        }

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && is_file($candidate) && is_readable($candidate)) {
                return [CURLOPT_CAINFO => $candidate];
            }
        }

        return [];
    }
}
