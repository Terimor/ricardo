<?php

namespace App\Exceptions;

use Exception;

class TxnNotFoundException extends Exception
{
    public $code = Handler::ECODE_TXN_NOT_FOUND;
    public $message = 'Txn not found';
}
