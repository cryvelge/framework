<?php

namespace App\Components\Message;

/**
 * 消息组，用来实现消息有序发送
 *
 * 消息的$data内容，僵尸一个Message数组
 *
 * @package App\Components\Message
 */
class MessageGroup extends Message {

    public function __construct($data)
    {
        parent::__construct($data);
        $this->topic = '_group';
    }

    /**
     * 不应该被调用
     */
    public function setTopic($topic)
    {
        // TODO: 不应该设置topic
    }

    /**
     * 序列化
     *
     * @return string 返回序列化结果
     */
    public function serialize()
    {
        $pending = $this->parse();
        return serialize($pending);
    }

    /**
     * 反序列化
     *
     * @param $serializeResult
     *
     * @return Message 返回一个MessageGroup对象
     */
    public static function unserialize($serializeResult): Message
    {
        $raw = unserialize($serializeResult);
        return self::inflate($raw);
    }

    /**
     * 用一个数组填充Message对象
     *
     * @param array $result
     *
     * @return Message 返回一个MessageGroup对象
     */
    public static function inflate($result)
    {
        $messages = array_map(function($raw_message) {
            return Message::inflate($raw_message);
        }, $result['data']);
        $message = new static($messages);
        $message->setId($result['id']);
        return $message;
    }

    /**
     * 将MessageGroup解析成一个数组
     *
     * @return array 返回数组
     */
    public function parse()
    {
        $pending = [
            'id' => $this->getId(),
            'topic' => $this->getTopic(),
        ];
        $data = [];
        for ($i = 0, $len = count($this->data); $i < $len; $i++) {
            $data[] = $this->data[$i]->parse();
        }
        $pending['data'] = $data;

        return $pending;
    }
}