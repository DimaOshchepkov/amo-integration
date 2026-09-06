<?php

namespace App\Exceptions;

use Exception;

class AmoCrmNotAuthorizedException extends Exception
{
    public function __construct(string $message = 'AmoCRM не авторизован')
    {
        parent::__construct($message);
    }
}
