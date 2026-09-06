<?php

namespace App\Exceptions\AmoAuth;

use Throwable;

class AuthorizationCodeMissingException extends AmoCrmOAuthException
{
    public function __construct(string $message = 'Code not received', ?Throwable $previous = null)
    {
        parent::__construct($message, $previous, 'Код авторизации не получен');
    }
}
