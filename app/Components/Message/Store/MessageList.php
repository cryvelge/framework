<?php

namespace App\Components\Message\Store;

use LaravelRedis;

use App\Components\Message\Scheduler;

/**
 * list格式存储消息内容
 *
 * @package App\Components\Message\Store
 */
class MessageList {

    /**
     * 加入数据
     *
     * @param string|array $id
     * @param string $timestamp
     */
    public static function push($id, $minute)
    {
        if (!is_array($id)) {
            $id = [$id];
        }
        $args = [Scheduler::$prefix.$minute];
        for ($i = 0, $len = count($id); $i < $len; $i++) {
            $args[] = $id[$i];
        }
        if (count($args) == 0) {
            return;
        }
        call_user_func_array([LaravelRedis::class, 'rpush'], $args);
    }

    /**
     * 获得对应分钟下的第一个消息
     *
     * @param int $minute
     *
     * @return string|boolean 消息的id | 当不存在时返回false 
     */
    public static function pop($minute) {
        return LaravelRedis::lpop(Scheduler::$prefix.$minute);
    }

    /**
     * 获得对应分钟下的第一个消息
     *
     * @param int $minute
     *
     * @return string|boolean 消息的id | 当不存在时返回false
     */
    public static function get($minute)
    {
        return LaravelRedis::lindex(Scheduler::$prefix.$minute, 0);
    }

    /**
     * @param $minute
     * @param $start
     * @param $count
     * @return mixed
     */
    public static function range($minute, $start, $count)
    {
        return LaravelRedis::lrange(Scheduler::$prefix.$minute, $start, $count);
    }

    /**
     * 删除minute或者指定其中的一个/多个message内容
     * @param $minute
     * @param string|array $id
     */
    public static function del($minute, $id = null)
    {
        if ($id == null) {
            LaravelRedis::del(Scheduler::$prefix.$minute);
        } else {
            if (!is_array($id)) {
                $id = [$id];
            }
            for ($i = 0, $len = count($id); $i < $len; $i++) {
                LaravelRedis::lrem(Scheduler::$prefix.$minute, 0, $id[$i]);
            }
        }
    }

    /**
     * 范围删除，或者范围搜索删除
     * @param $start_minute
     * @param $end_minute
     * @param string|array $message
     */
    public static function delRange($start_minute, $end_minute, $id = null)
    {
        for ($minute = $start_minute, $len = $end_minute; $minute < $len; $minute++) {
            self::del($minute, $id);
        }
    }

    /**
     *
     */
    public static function count($minute)
    {
        return LaravelRedis::llen(Scheduler::$prefix.$minute);
    }
}