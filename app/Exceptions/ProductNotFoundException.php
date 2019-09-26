<?php

namespace App\Exceptions;

use Exception;

class ProductNotFoundException extends Exception
{
    public $code = 10001;
    public $message = 'OdinProduct not found';
}
