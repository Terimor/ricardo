<?php

namespace App\Exceptions;

use Exception;

class PaymentException extends Exception
{
    public $code = 10005;
    public $message = 'Payment can\'t be processed';
}
