<?php

namespace App\Exceptions;

use Exception;

class OrderNotFoundException extends Exception
{
    public $code = 10006;
    public $message = 'OdinOrder not found';
}
