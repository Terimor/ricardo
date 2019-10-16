<?php

namespace App\Exceptions;

use Exception;

class ProductNotFoundException extends Exception
{
    public $code = Handler::ECODE_PRODUCT_NOT_FOUND;
    public $message = 'OdinProduct not found';
}
