<?php

namespace App\Library\Exceptions;

abstract class Exception extends \Exception
{
    public abstract function getExceptionLiteral() : string;
}
