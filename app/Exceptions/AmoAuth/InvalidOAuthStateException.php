<?php

namespace App\Exceptions\AmoAuth;

use Throwable;

class InvalidOAuthStateException extends AmoCrmOAuthException
{
    public function __construct(string $message = 'State mismatch', ?Throwable $previous = null)
    {
        parent::__construct($message, $previous, 'Неверный параметр безопасности');
    }
}
