<?php

namespace App\Exceptions;

use Exception;

class PPCurrencyNotSupportedException extends Exception
{
    public $code = 10008;
    public $message = 'Currency is not supported';
}
