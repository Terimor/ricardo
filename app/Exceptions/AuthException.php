<?php

namespace App\Exceptions;

use Exception;

class AuthException extends Exception
{
    public $code = Handler::ECODE_AUTH;
    public $message = 'Unauthorized';
}
