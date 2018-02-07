<?php

namespace App\Components\Message\Notifier;

use App\Components\Message\INotifier;
use App\Components\Message\MessageSendResult;
use App\Components\Message\Models\Notification;

/**
 * Class NotificationNotifier
 *
 * @package App\Components\Message\Notifier
 */
class NotificationNotifier implements INotifier
{
    /**
     * 发送通知
     *
     * @param Message $message 消息对象
     *
     * @return MessageSendResult 消息发送结果
     */
    public function send($message) : MessageSendResult
    {
        $notification = new Notification();

        $notification->user_id = $message->getUserId();
        $notification->msg_id = $message->getId();
        $notification->title = $message->title;
        $notification->body = $message->body;
        $notification->url = $message->url;
        $notification->pic = $message->pic;
        $notification->type = Notification::TYPE_SYSTEM;
        $notification->status = Notification::STATUS_PENDING;

        $saved = $notification->save();

        $result = new MessageSendResult();
        if ($saved != null) {
            $result->result = 0;
            $result->response = $notification->toArray();
        } else {
            $result->result = 1;
            $result->error = new \Exception('insert notification fail');
        }
        return $result;
    }
}