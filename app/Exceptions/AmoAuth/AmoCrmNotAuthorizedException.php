<?php

namespace App\Exceptions\AmoAuth;

use Throwable;

class AmoCrmNotAuthorizedException extends AmoCrmOAuthException
{
    public function __construct(string $message = 'AmoCRM не авторизован', ?Throwable $previous = null)
    {
        parent::__construct($message, $previous, 'AmoCRM не подключена');
    }
}
