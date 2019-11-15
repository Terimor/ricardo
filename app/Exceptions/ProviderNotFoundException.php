<?php

namespace App\Exceptions;

class ProviderNotFoundException extends PhraseExtendedException
{
    public $code    = Handler::ECODE_PROVIDER_NOT_FOUND;
    public $message = 'Provider not found';
    public $phrase  = 'card.error.cannot_be_processed';
}
