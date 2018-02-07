<?php

namespace App\Components\Card;

use App\Components\Card\Type\Demo;

class Manager
{
    const TYPE_DEMO = Demo::class;

    public static function init(string $type, &$data)
    {
        return new $type($data);
    }
}
