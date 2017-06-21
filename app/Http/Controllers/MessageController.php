<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MessageController
{
    public function wechat()
    {
        $wechat = app('wechat');
        $wechat->server->setMessageHandler(function($message){
            return "欢迎关注 overtrue！";
        });

        return $wechat->server->serve();
    }

    public function wxapp(Request $request)
    {
        //
    }
}
