<?php

namespace App\Exceptions;

use Exception;

class OrderUpdateException extends Exception
{
    public $code = 10003;
    public $message = 'OdinOrder can\'t be updated';
}
