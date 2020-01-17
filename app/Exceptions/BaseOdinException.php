<?php

namespace App\Exceptions;

class BaseOdinException extends \Exception
{
    public $params = [];

    public function __construct($params, $message)
    {
        if (!empty($params)) {
            $this->params = $params;
        }
        parent::__construct($message);
    }

    /**
     * Returns params
     * @return string
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
