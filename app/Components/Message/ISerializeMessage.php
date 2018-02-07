<?php

namespace App\Components\Message;

interface ISerializeMessage
{
    /**
     * 序列化
     * @return string 返回序列化的结果
     */
    public function serialize();

    /**
     * 反序列化
     *
     * @param {string} $serializeResult
     *
     * @return {Message} 返回消息对象
     */
    public static function unserialize($serializeResult);
}