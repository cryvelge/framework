<?php

namespace App\Library;

trait SingletonTrait
{
    /**
     * @var static
     */
    protected static $instance;

    /**
     * @return static
     */
    public static function instance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }
}
