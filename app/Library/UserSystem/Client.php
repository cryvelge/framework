<?php

namespace App\Library\UserSystem;

use App\Library\SingletonTrait;
use Requests;

class Client
{
    use SingletonTrait;

    /**
     * @var string
     */
    private $host;

    private function __construct()
    {
        $this->host = env('USER_SYSTEM_HOST', 'http://user_system.dev');
    }

    public function register($openId, $unionId, $platformId)
    {
        $url = "{$this->host}/clients/create";
        $response = Requests::post($url, [], [
            'token' => $openId,
            'union_id' => $unionId,
            'platform_id' => $platformId
        ]);
        $body = $response->body;

        $ret = json_decode($body);

        if ($ret->status == 0) {
            return $ret->data;
        } else {
            throw new \Exception($ret->message);
        }
    }

    public function subscribe($openId, $platformId)
    {
        $url = "{$this->host}/clients/update";
        $response = Requests::post($url, [], [
            'token' => $openId,
            'platform_id' => $platformId,
            'subscribe' => 1
        ]);
        $body = $response->body;

        $ret = json_decode($body);

        if ($ret->status == 0) {
            return $ret->data;
        } else {
            throw new \Exception($ret->message);
        }
    }

    public function unsubscribe($openId, $platformId)
    {
        $url = "{$this->host}/clients/update";
        $response = Requests::post($url, [], [
            'token' => $openId,
            'platform_id' => $platformId,
            'subscribe' => 0
        ]);
        $body = $response->body;

        $ret = json_decode($body);

        if ($ret->status == 0) {
            return $ret->data;
        } else {
            throw new \Exception($ret->message);
        }
    }
}
