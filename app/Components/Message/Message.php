<?php

namespace App\Components\Message;

use App\Components\Message\Manager;
use App\Components\Message\MessageSendResult;

/**
 * 
 * @package App\Components\Message
 */
class Message implements ISerializeMessage
{
    // 消息id
    protected $id = null;
    // 用户id
    protected $user_id = null;
    // 第三方id
    protected $third_id = null;
    // topic
    protected $topic = null;
    // 消息数据
    protected $data = [];

    // 构造函数
    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * 获得数据
     * @param $key
     * @return mixed
     */
    public function getValue($key) {
        return $this->data[$key];
    }

    /**
     * 获得data
     */
    public function getData() {
        return $this->data;
    }

    /**
     * 获得消息id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * 获得user_id
     */
    public function getUserId() {
        return $this->user_id;
    }

    /**
     * 获得third_id
     */
    public function getThirdId() {
        return $this->third_id;
    }

    /**
     * 获得topic
     * @return string
     */
    public function getTopic() {
        return $this->topic;
    }

    /**
     * 设置数据内容
     * 
     * @param {} $key
     * @param {} $value
     *
     * @return $this 
     */
    public function setValue($key, $value) {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * 
     * @return $this
     */
    public function setData($data) {
        $this->data = $data;
        return $this;
    }

    /**
     * 设置id
     * @return $this
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * 设置user_id
     *
     * @param $user_id
     * @return $this
     */
    public function setUserId($user_id) {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * 设置third_id
     *
     * @param $third_id
     * @return $this
     */
    public function setThirdId($third_id) {
        $this->third_id = $third_id;
        return $this;
    }

    /**
     * 设置topic
     *
     * @param $topic
     * @return $this
     */
    public function setTopic($topic) {
        $this->topic = $topic;
        return $this;
    }

    /**
     * 同步发送
     *
     * @return MessageSendResult 返回发送结果
     */
    public function sendSync()
    {
        return Manager::instance()->sendSync($this);
    }

    /**
     * 异步发送
     */
    public function send()
    {
        Manager::instance()->send($this);
        return $this;
    }

    /**
     * 延迟发送
     *
     * @param int $delay 延迟秒数
     */
    public function sendAfter($delay)
    {
        Manager::instance()->sendAfter($this, $delay);
        return $this;
    }

    /**
     * 定时发送
     *
     * @param int $timestamp 定时发送时间
     */
    public function sendAt($timestamp)
    {
        Manager::instance()->sendAt($this, $timestamp);
        return $this;
    }

    // ISerializeMessage
    /**
     * 序列化自己
     *
     * @return string
     */
    public function serialize() {
        return serialize($this->parse());
    }

    /**
     * 反序列化
     * @param $serializeResult
     *
     * @return {Message}
     */
    public static function unserialize($serializeResult) : Message
    {
        $result = unserialize($serializeResult);
        return self::inflate($result);
    }

    /**
     * 将message对象转换成一个数组
     *
     * @return array 
     */
    public function parse()
    {
        return [
            'id' => $this->getId(),
            'user_id' => $this->getUserId(),
            'third_id' => $this->getThirdId(),
            'topic' => $this->getTopic(),
            'data' => $this->data,
        ];
    }

    /**
     * 用一个数组填充Message对象
     *
     * @param $result
     * @return static
     */
    public static function inflate($result) {
        $message = new static($result['data']);
        $message->setId($result['id']);
        $message->setUserId($result['user_id']);
        $message->setThirdId($result['third_id'] ?? null);
        $message->setTopic($result['topic']);
        return $message;
    }

    // magic function
    public function __get($name) {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        } else {
            return null;
        }
    }

    public function __set($name, $value) {
        $this->data[$name] = $value;
    }
}