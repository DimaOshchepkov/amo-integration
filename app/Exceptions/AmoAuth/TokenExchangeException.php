<?php

namespace App\Exceptions\AmoAuth;

use Throwable;

class TokenExchangeException extends AmoCrmOAuthException
{
    public function __construct(string $message = 'Token exchange failed', ?Throwable $previous = null)
    {
        parent::__construct($message, $previous, 'Не удалось получить токен доступа');
    }
}
