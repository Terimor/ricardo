<?php

namespace App\Exceptions;

use Exception;

class CustomerUpdateException extends Exception
{
    public $code = 10002;
    public $message = 'OdinCustomer can\'t be updated';
}
