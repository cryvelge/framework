<?php

namespace App\Components\Message;

use App\Components\Message\Store\MessageBody;
use App\Components\Message\Store\MessageList;
use App\Components\Message\Store\MessageMinute;
use App\Components\Message\Jobs\SendScheduleMessage;

/**
 * Class Scheduler
 *
 * 定时消息相关数据的存储和处理
 *
 * @package App\Components\Message
 */
class Scheduler {

    /**
     * @var string 缓存前缀
     */
    public static $prefix = 'message_schedule_';

    /**
     * 发送scheduleMessage
     *
     * @param int $minute
     */
    public static function sendScheduleMessage($minute) {
        // 发送消息
        $job = new SendScheduleMessage($minute);
        $queue = config('message.queue');
        if ($queue != null) {
            $job->onQueue($queue);
        }
        dispatch($job);
    }

    /**
     * 方便地添加一个消息
     *
     * @param Message $message
     */
    public static function simpleSet(Message $message, int $timestamp) {
        $minute = (int)($timestamp / 60);
        MessageList::push($message->getId(), $minute);
        MessageBody::set($message);
        MessageMinute::set($message->getId(), $timestamp);
    }

    /**
     * 批量设置消息
     *
     * schedule_message: {message: Message, timestamp: int}
     *
     * @param array $schedule_messages 消息数据
     */
    public static function set(array $schedule_messages) {
        // 所有需要处理的list
        $list = [];
        for ($i = 0, $len = count($schedule_messages); $i < $len; $i++) {
            $schedule_message = $schedule_messages[$i];
            $minute = (int)($schedule_message['timestamp'] / 60);
            $message = $schedule_message['message'];
            if (!isset($list[$minute])) {
                $list[$minute] = [];
            }
            $list[$minute][] = $message;
        }
        $minutes = array_keys($list);
        for ($i = 0, $len = count($minutes); $i < $len; $i++) {
            $minute = $minutes[$i];
            $ids = [];
            $messages = [];
            for ($i = 0, $len = count($schedule_messages); $i < $len; $i++) {
                $ids[] = $schedule_messages[$i]['message']->getId();
                $messages[] = $schedule_messages[$i]['message'];
            }

            MessageList::push($ids, $minute);
            MessageBody::set($messages);
        }

        // 设置minuteMap数据
        $pairs = array_map(function($schedule_message) {
            return [
                'id' => $schedule_message['message']->getId(),
                'timestamp' => $schedule_message['timestamp'],
            ];
        }, $schedule_messages);
        MessageMinute::setPairs($pairs);
    }

    /**
     * 获得某一分钟中应该发出的消息内容
     *
     * @param int $minute 分钟数
     *
     * @return array $messages 返回所有在这一分钟中应该发出的消息
     */
    public static function get($minute)
    {
        $count = MessageList::count($minute);
        $ids = MessageList::range($minute, 0, $count);
        $rawMessages = MessageBody::get($ids);
        $messages = array_map(function($id) use($rawMessages) {
            $rawMessage = $rawMessages[$id];
            $raw = unserialize($rawMessage);
            if ($raw['topic'] == '_group') {

            } else {

            }
            return Message::unserialize($rawMessage);
        }, $ids);
        return $messages;
    }

    /**
     * 根据消息id删除存储在缓存中的消息信息
     *
     * @param string|array $id
     */
    public static function removeById($id)
    {
        $minute = MessageMinute::get($id);
        MessageList::del($minute, $id);
        MessageBody::del($id);
        MessageMinute::del($id);
    }
}