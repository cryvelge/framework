<?php

namespace App\Components\User;

use App\Components\User\Models\Client;
use App\Components\User\Models\User;
use App\Library\SingletonTrait;

class Manager
{
    use SingletonTrait;

    private $wxappAppId;
    private $wxappAppSecret;
    private $wechatAppId;
    private $wechatAppSecret;

    private function __construct()
    {
        $this->wxappAppId = config('wxapp.app_id');
        $this->wxappAppSecret = config('wxapp.app_secret');
        $this->wechatAppId = config('wechat.app_id');
        $this->wechatAppSecret = config('wechat.app_secret');
    }

    public function loginWXApp($openId, $sessionKey, $userInfo)
    {
        $client = Client::findByWxAppOpenId($openId);
        if (is_null($client)) {
            $client = Client::registerWxApp($openId, $sessionKey, $userInfo);
        } else {
            $client->updateSessionKey($sessionKey);
        }

        session(['user' => $client->user()]);
    }
}
