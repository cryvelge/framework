<?php

namespace App\Components\User\Models;

use App\Library\Model\Model;
use App\Library\UserSystem\Client as UserSystemClient;
use App\Library\UserSystem\User as UserSystemUser;

class Client extends Model
{
    protected static $columns = [
        'id',
        'user_id',
        'open_id',
        'type',
        'session_key',
        'subscribe',
        'subscribe_at',
        'unsubscribe_at',
        'first_subscribe_at',
        'created_at',
        'updated_at',
    ];

    /**
     * @param string $openId
     * @return Client
     */
    public static function findByWxAppOpenId($openId)
    {
        return static::where('openid', $openId)->where('type', 'wxapp')->first();
    }

    /**
     * @param string $openId
     * @return Client
     */
    public static function findByWeChatOpenId($openId)
    {
        return static::where('openid', $openId)->where('type', 'wechat')->first();
    }

    /**
     * @param string $openId
     * @param string $sessionKey
     * @param array $userInfo
     * @return Client
     */
    public static function registerWxApp($openId, $sessionKey, $userInfo)
    {
        $unionId = $userInfo['union_id'];

        $registerResult = UserSystemClient::instance()->register($openId, $unionId, env('USER_SYSTEM_PLATFORM_ID'));
        if ($registerResult->is_new_user) {
            UserSystemUser::instance()->updateUserInfo($registerResult->user->serial_number, $userInfo);
        }

        $user = User::findByUnionId($unionId);

        if (is_null($user)) {
            $user = User::register(array_merge($userInfo, [
                'union_id' => $unionId,
                'serial_number' => $registerResult->user->serial_number
            ]));
        }

        $client = static::create([
            'user_id' => $user->id,
            'open_id' => $openId,
            'type' => 'wxapp',
            'session_key' => $sessionKey,
            'subscribe' => true
        ]);

        return $client;
    }

    /**
     * @param string $sessionKey
     */
    public function updateSessionKey($sessionKey)
    {
        $this->session_key = $sessionKey;
        $this->save();
    }

    /**
     * @return User
     */
    public function user()
    {
        return User::find($this->user_id);
    }
}
