<?php

namespace App\Components\Message;

use App\Components\Message\Exceptions\MessageInvalidTopicException;
use App\Components\Message\Store\MessageId;
use Carbon\Carbon;
use Illuminate\Support\Str;

use App\Jobs\SendMessage;
use App\Library\SingletonTrait;

// 内部
use App\Components\Message\Message;
use App\Components\Message\Event\MessageSent;

/**
 * 
 * @package App\Components\Message
 */
class Manager
{
    use SingletonTrait;

    // 所有的notifier
    protected $topic_map_notifier = [];

    // 所有的reader
    protected $topic_map_reader = [];

    /**
     * 生成Message对象(工厂模式)
     *
     * @param $data
     * @return Message
     */
    public function generate($data) : Message
    {
        $message = new Message($data);
        $message->setId(MessageId::get());
        return $message;
    }

    /**
     * 生成MessageGroup对象
     *
     * @param array $messages 消息数组
     *
     * @return MessageGroup 
     */
    public function generateGroup(array $messages) : MessageGroup
    {
        $group = new MessageGroup($messages);
        $group->setId(MessageId::get());
        return $group;
    }

    /**
     * 同步发送消息
     *
     * @param Message|array $message
     *
     * @return MessageSendResult|array 消息发送结果
     */
    public function sendSync($message) {
        $is_array = is_array($message);
        if (!$is_array) {
            $message = [$message];
        }
        $result = [];
        for ($i = 0, $len = count($message); $i < $len; $i++) {
            $topic = $message[$i]->getTopic();
            if ($topic != null) {
                $notifier = $this->getNotifier($topic);
                $send_result = $notifier->send($message[$i]);
            } else {
                $send_result = new MessageSendResult();
                $send_result->result = 1;
                $e = new MessageInvalidTopicException($message->getId(), $message->getTopic());
                $send_result->error = [
                    'message' => $e->getMessage(),
                ];
            }
            event(new MessageSent($message[$i], $send_result)); // 发送事件
            $result[] = $send_result;
        }
        if (!$is_array) {
            return $result[0];
        } else {
            return $result;
        }
    }

    /**
     * 异步发送消息，是sendAfter($message, 0, $together)的简写
     *
     * @param Message|array $message 允许接收一个数组
     * @param boolean $together 是否将消息组成MessageGroup，以保证顺序发送
     */
    public function send($message, $together = false) {
        return self::sendAfter($message, 0, $together);
    }

    /**
     * 延迟发送消息
     *
     * @param Message|array $message
     * @param int $delay 以秒为单位，如果设置为0，消息将立刻进入队列进行处理
     * @param boolean $together 是否将消息组成MessageGroup，以保证顺序发送
     */
    public function sendAfter($message, $delay, $together = false) {
        if ($together) {
            if (!is_array($message)) {
                $message = $this->generateGroup([$message]); // 当不是数组的时候改为一个数组
            } else {
                $message = $this->generateGroup($message);
            }
        }
        $job = new SendMessage($message);
        if ($delay > 0) { // TODO: 需要设置阈值
            $job->delay(Carbon::now()->addSeconds($delay));
        }
        $queue = config('message.queue');
        if ($queue != null) {
            $job->onQueue($queue);
        }
        dispatch($job);
    }

    /**
     * 发送消息
     *
     * @param Message|array $message 消息对象
     * @param int $timestamp
     * @param boolean $together 是否将消息组成MessageGroup，以保证顺序发送
     */
    public function sendAt($message, $timestamp, $together = false) {
        if ($together) {
            if (!is_array($message)) {
                $message = $this->generateGroup([$message]); // 当不是数组的时候改为一个数组
            } else {
                $message = $this->generateGroup($message);
            }
        }
        if (is_array($message)) {
            $schedule_messages = array_map(function($message) use ($timestamp) {
                return [
                    'message' => $message,
                    'timestamp' => $timestamp,
                ];
            }, $message);
            Scheduler::set($schedule_messages);
        } else {
            Scheduler::simpleSet($message, $timestamp);
        }
    }

    /**
     * 注册notifier
     *
     * @param string $topic
     * @param INotifier $notifier
     *
     * @return boolean 注册成功返回true，否则返回false 
     */
    public function registerNotifier($topic, $notifier) {
        if (!isset($this->topic_map_notifier[$topic])) {
            $this->topic_map_notifier[$topic] = $notifier;
            return true; // 注册成功
        } else {
            return false;
        }
    }

    /**
     * 获得对应的notifier，当不存在对应的notifier时，返回null
     *
     * @param string $topic
     *
     * @return INotifier|null 返回一个notifier对象
     */
    protected function getNotifier($topic) {
        if (isset($this->topic_map_notifier[$topic])) {
            return $this->topic_map_notifier[$topic];
        } else {
            return null;
        }
    }

    /**
     * 注册reader
     *
     * @param string $topic
     * @param $reader 
     *
     * @return boolean 注册成功返回true，否则返回false 
     */
    public function registerReader($topic, $reader) {
        if (!isset($this->topic_map_reader[$topic])) {
            $this->topic_map_reader[$topic] = $reader;
            return true;
        } else {
            return false;
        }
    }

    /**
     * 找到reader
     *
     * @param string $topic
     *
     * @return 返回一个Reader对象或者null
     */
    public function getReader($topic) {
        if (isset($this->topic_map_reader[$topic])) {
            return $this->topic_map_reader[$topic];
        } else {
            return null;
        }
    }
}