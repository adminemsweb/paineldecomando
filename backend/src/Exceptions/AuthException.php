<?php
declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class AuthException extends RuntimeException
{
    public function __construct(string $message, public readonly int $status = 400)
    {
        parent::__construct($message);
    }
}
