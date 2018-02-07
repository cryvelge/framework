<?php

namespace App\Components\Message\Store;

use LaravelRedis;

use App\Components\Message\Message;
use App\Components\Message\Scheduler;

/**
 * Class MessageBody
 * 对于消息体的处理
 *
 *  保存 msg_id -> 序列化后结果
 *
 * @package App\Components\Message\Store
 */
class MessageBody {

    /**
     * 设置数据
     *
     * @param Message|array $message
     */
    public static function set($message)
    {
        if (!is_array($message)) {
            $message = [$message];
        }
        $args = [];
        for ($i = 0, $len = count($message); $i < $len; $i++) {
            $args[$message[$i]->getId()] = $message[$i]->serialize();
        }
        LaravelRedis::hmset(Scheduler::$prefix.'body', $args);
    }

    /**
     * 根据消息id获得消息内容
     *
     * @param string|array $id 消息id
     *
     * @return string|array|bool 所有的消息的id对应的body | 消息id和body组成的map(其中的value可能存在false) | 消息id不存在时返回false
     */
    public static function get($id)
    {
        $is_array = is_array($id);
        if (!$is_array) {
            $id = [$id];
        }
        // 要求最终的id是一个array
        $result = LaravelRedis::hmget(Scheduler::$prefix.'body', $id);
        if ($is_array) {
            return $result;
        } else {
            return $result[$id[0]];
        }
    }

    /**
     * 删除所有的id
     *
     * @param string|array $id
     *
     * @return void
     */
    public static function del($id)
    {
        if (!is_array($id)) {
            $id = [$id];
        }
        array_unshift($id, Scheduler::$prefix.'body');
        call_user_func_array([LaravelRedis::class, 'hdel'], $id);
    }

    /**
     * 删除消息i
     *
     * @param Message|array $message
     *
     * @return void
     */
    public static function delMessage($message) {
        if (!is_array($message)) {
            $message = [$message];
        }
        $id = array_map(function($message) {
            return $message->getId();
        }, $message);
        self::del($id);
    }

    /**
     * 获得当前message的数量
     *
     * @return int 消息数量
     */
    public static function count() {
        return LaravelRedis::hlen(Scheduler::$prefix.'body');
    }

    /**
     * 销毁消息体所有缓存数据
     */
    public static function destroy() {
        LaravelRedis::del(Scheduler::$prefix.'body');
    }
}