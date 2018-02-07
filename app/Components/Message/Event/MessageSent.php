<?php

namespace App\Components\Message\Event;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

use App\Components\Message\Message;
use App\Components\Message\MessageSendResult;

class MessageSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // 消息内容
    protected $message = null;
    // 发送结果
    protected $result = null;

    /**
     * Create a new event instance.
     *
     * @param Message $message
     * @param MessageSendResult $result
     *
     * @return void
     */
    public function __construct($message, $result)
    {
        $this->message = $message;
        $this->result = $result;
    }

    /**
     * 获得消息对象
     *
     * @return Message 消息对象
     */
    public function getMessage() : Message
    {
        return $this->message;
    }

    /**
     * 获得发送结果
     *
     * @return MessageSendResult 消息发送结果
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
