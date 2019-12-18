<?php

namespace App\Exceptions;

class InvalidParamsException extends \Exception
{
    public $code    = Handler::ECODE_INVALID_PARAMS;
    public $message = 'Invalid Params';
}
