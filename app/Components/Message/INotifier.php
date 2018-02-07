<?php

namespace App\Components\Message;

interface INotifier {

    /**
     * 发送消息
     *
     * @param Message $message
     *
     * @return MessageSendResult
     */
    public function send($message) : MessageSendResult;
}