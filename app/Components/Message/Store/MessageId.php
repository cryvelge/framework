<?php

namespace App\Components\Message\Store;

use LaravelRedis;

/**
 * 消息自增id
 *
 * @package App\Components\Message\Store
 */
class MessageId
{
    /**
     * 获得message的id
     *
     * @return mixed
     */
    public static function get()
    {
        return LaravelRedis::incr('message_message_id', 1);
    }

    /**
     * 销毁数据
     */
    public static function destroy()
    {
        LaravelRedis::del('message_message_id');
    }
}