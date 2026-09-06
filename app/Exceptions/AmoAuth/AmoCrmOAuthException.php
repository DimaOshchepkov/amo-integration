<?php

namespace App\Exceptions\AmoAuth;

use Exception;
use Throwable;

abstract class AmoCrmOAuthException extends Exception
{
    public function __construct(
        string $message = 'OAuth error',
        ?Throwable $previous = null,
        protected string $userMessage = 'Произошла ошибка при авторизации'
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getUserMessage(): string
    {
        return $this->userMessage;
    }
}
