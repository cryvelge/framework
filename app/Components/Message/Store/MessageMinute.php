<?php

namespace App\Components\Message\Store;

use LaravelRedis;

use App\Components\Message\Scheduler;

/**
 * 存储 msg_id -> minute 的数据
 *
 * @package App\Components\Message\Store
 */
class MessageMinute {
    /**
     * 设置数据
     *
     * @param string|array $id
     * @param $timestamp
     */
    public static function set($id, $timestamp)
    {
        if (!is_array($id)) {
            $id = [$id];
        }
        $args = [];
        $minute = (int)($timestamp / 60);
        for ($i = 0, $len = count($id); $i < $len; $i++) {
            $args[$id[$i]] = $minute;
        }
        LaravelRedis::hmset(Scheduler::$prefix.'minute', $args);
    }

    /**
     * 处理所有的pairs
     *
     * @param array<id, timestamp> $pairs
     */
    public static function setPairs($pairs)
    {
        $args = [];
        for ($i = 0, $len = count($pairs); $i < $len; $i++) {
            $args[$pairs[$i]['id']] = (int)($pairs[$i]['timestamp'] / 60);
        }
        LaravelRedis::hmset(Scheduler::$prefix.'minute', $args);
    }

    /**
     * 获得id对应的minute值
     *
     * @param string|array $id
     *
     * @return int|array 分钟信息
     */
    public static function get($id)
    {
        $is_array = is_array($id);
        if (!$is_array) {
            $id = [$id];
        }
        $result = LaravelRedis::hmget(Scheduler::$prefix.'minute', $id);
        if ($is_array) {
            return $result;
        } else {
            return $result[$id[0]];
        }
    }

    /**
     * 删除id对应的minute数据
     *
     * @param string|array $id
     */
    public static function del($id)
    {
        if (!is_array($id)) {
            $id = [$id];
        }
        array_unshift($id, Scheduler::$prefix.'minute');
        call_user_func_array([LaravelRedis::class, 'hdel'], $id);
    }

    /**
     * 检查数量
     *
     * @return mixed
     */
    public static function count()
    {
        return LaravelRedis::hlen(Scheduler::$prefix.'minute');
    }

    /**
     * 销毁缓存
     */
    public static function destroy()
    {
        LaravelRedis::del(Scheduler::$prefix.'minute');
    }
}