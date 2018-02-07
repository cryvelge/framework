<?php

namespace App\Http\Controllers\External;

use App\Components\MessageHandler\Manager as MessageHandler;
use App\Components\WeChatPay\Manager as WeChatPayManager;
use EasyWeChat;
use Illuminate\Http\Request;

class WeChatController
{
    public function message()
    {
        $server = EasyWeChat::server();
        $server->setMessageHandler(function ($message) {
            return MessageHandler::handle($message);
        });

        return $server->serve();
    }

    public function paymentNotify()
    {
        $payment = EasyWeChat::payment();
        $response = $payment->handleNotify(function($notify, $successful){
            WeChatPayManager::notifyWeChatPay($notify->toArray(), $successful);
            return true;
        });

        return $response;
    }
}
