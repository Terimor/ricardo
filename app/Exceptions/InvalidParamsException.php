<?php

namespace App\Exceptions;

use Exception;

class InvalidParamsException extends Exception
{
    public $code    = Handler::ECODE_INVALID_PARAMS;
    public $message = 'Invalid Params';
}
