<?php

namespace App\Exceptions;

use Exception;

class TxnNotFoundException extends Exception
{
    public $code = 10007;
    public $message = 'Txn not found';
}
