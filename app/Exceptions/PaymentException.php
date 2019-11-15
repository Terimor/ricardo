<?php

namespace App\Exceptions;

class PaymentException extends PhraseExtendedException
{
    public $code    = Handler::ECODE_PAYMENT;
    public $message = 'Payment can\'t be processed';
    public $phrase  = 'card.error.cannot_be_processed';
}
