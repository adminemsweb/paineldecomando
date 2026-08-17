<?php

declare(strict_types=1);

// O servidor embutido do PHP precisa deste roteador durante o desenvolvimento.
// Arquivos públicos existentes são entregues normalmente; o restante segue para a API.
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$decodedPath = rawurldecode(is_string($requestPath) ? $requestPath : '/');
$publicRoot = realpath(__DIR__);
$requestedFile = realpath(__DIR__ . DIRECTORY_SEPARATOR . ltrim($decodedPath, '/\\'));

if (
    $publicRoot !== false
    && $requestedFile !== false
    && str_starts_with($requestedFile, $publicRoot . DIRECTORY_SEPARATOR)
    && is_file($requestedFile)
) {
    return false;
}

require __DIR__ . '/index.php';
