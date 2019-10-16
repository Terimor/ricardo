<?php

namespace App\Exceptions;

use Exception;

class OrderUpdateException extends Exception
{
    public $code = Handler::ECODE_ORDER_UPDATE;
    public $message = 'OdinOrder can\'t be updated';
}
