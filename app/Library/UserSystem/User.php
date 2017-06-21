<?php

namespace App\Library\UserSystem;

use App\Library\SingletonTrait;
use Requests;

class User
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

    public function updateUserInfo($serialNumber, $data)
    {
        $url = "{$this->host}/users/update";

        $data = array_merge([
            'serial_number' => $serialNumber
        ], array_only($data, ['nickname', 'avatar', 'gender', 'country', 'province', 'city', 'address']));

        $response = Requests::post($url, [], $data);
        $body = $response->body;

        $ret = json_decode($body);

        if ($ret->status == 0) {
            return $ret->data;
        } else {
            throw new \Exception($ret->message);
        }
    }
}
