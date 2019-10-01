<?php

namespace App\Exceptions;

use Exception;

class AuthException extends Exception
{
    public $code = 10401;
    public $message = 'Authorization error';
}
