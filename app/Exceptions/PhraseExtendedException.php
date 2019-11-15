<?php

namespace App\Exceptions;

class PhraseExtendedException extends \Exception
{
    public $phrase  = 'card.error.common';

    public function __construct(string $message, string $phrase = '', int $code = 0, Exception $previous = null)
    {
        if (!empty($phrase)) {
            $this->phrase = $phrase;
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns phrase for translation
     * @return string
     */
    public function getPhrase(): string
    {
        return $this->phrase;
    }
}
