<?php

namespace App\Exceptions;

use Exception;

class OrderNotFoundException extends Exception
{
    public $code = Handler::ECODE_ORDER_NOT_FOUND;
    public $message = 'OdinOrder not found';
}
