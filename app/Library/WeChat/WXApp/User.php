<?php

namespace App\Library\WXApp;

use App\Library\SingletonTrait;
use App\Library\WeChat\Crypto\WXBizDataCrypt;
use Requests;

class User
{
    use SingletonTrait;

    private $appId;
    private $appSecret;

    private function __construct()
    {
        $this->appId = config('wxapp.app_id');
        $this->appSecret = config('wxapp.app_secret');
    }

    public function getUserSessionKey($code)
    {
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$this->appId}&secret={$this->appSecret}&js_code={$code}&grant_type=authorization_code";
        $response = Requests::get($url);
        $data = json_decode($response->body);

        $openId = $data->openid;
        $sessionKey = $data->session_key;

        return [$openId, $sessionKey];
    }

    public function decryptUserInfo($rawData, $encryptedData, $iv, $signature)
    {
        $expectedSignature = sha1($rawData . $sessionKey);

        if ($signature !== $expectedSignature) {
            throw new \Exception('signature not match');
        }

        $instance = new WXBizDataCrypt($this->appId, $sessionKey);
        $userInfo = $instance->decryptData($encryptedData, $iv);

        $userInfo = json_decode($userInfo);
        return [
            'nickname' => $userInfo['nickName'],
            'avatar' => $userInfo['avatarUrl'],
            'gender' => $userInfo['gender'],
            'country' => $userInfo['country'],
            'province' => $userInfo['province'],
            'city' => $userInfo['city'],
        ];
    }
}
