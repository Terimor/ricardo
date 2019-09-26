<?php

namespace App\Exceptions;

use Exception;

class InvalidParamsException extends Exception
{
    public $code = 10004;
    public $message = 'Invalid Params';
}
