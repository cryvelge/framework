<?php

namespace App\Library\Exceptions;

class StandardError extends Exception
{
    protected $literalMessage;

    public function __construct($message = '服务器繁忙，请稍后重试。', ...$args)
    {
        $this->literalMessage = $message;
        parent::__construct($message, ...$args);
    }

    public  function getExceptionLiteral(): string
    {
        return $this->literalMessage;
    }
}
