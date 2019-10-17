<?php

namespace App\Exceptions;

use Exception;

class PPCurrencyNotSupportedException extends Exception
{
    public $code = Handler::ECODE_PP_CUR_NOT_SUPPORTED;
    public $message = 'Currency is not supported';
}
