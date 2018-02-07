<?php

namespace App\Components\Message\Facade;

use Illuminate\Support\Facades\Facade;

class Message extends Facade
{
    /**
     * 获取组件注册名称
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'message';
    }
}