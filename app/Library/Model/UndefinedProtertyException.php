<?php

namespace App\Library\Model;

class UndefinedProtertyException extends \Exception
{
    public function __construct($class, $property)
    {
        parent::__construct("Undefined property {$property} of {$class}");
    }
}
