<?php

namespace App\Exceptions;

use Exception;

class PaymentException extends Exception
{
    public $code = Handler::ECODE_PAYMENT;
    public $message = 'Payment can\'t be processed';
}
