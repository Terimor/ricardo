<?php

namespace App\Exceptions;

class CustomerUpdateException extends \Exception
{
    public $code = Handler::ECODE_CUSTOMER_UPDATE;
    public $message = 'OdinCustomer can\'t be updated';
}
