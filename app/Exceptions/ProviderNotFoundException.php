<?php

namespace App\Exceptions;

use Exception;

class ProviderNotFoundException extends Exception
{
    public $code = Handler::ECODE_PROVIDER_NOT_FOUND;
    public $message = 'Provider not found';
}
