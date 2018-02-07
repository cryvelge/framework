<?php

namespace App\Components\Message\Listener;

use App\Components\Message\Event\MessageSent;
use App\Components\Message\Store\MessageBody;
use App\Components\Message\Store\MessageMinute;
use Splunk;

class Listener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  MessageSent $event
     * @return void
     */
    public function handle(MessageSent $event)
    {
        // TODO: 发送消息给splunk
        $message = $event->getMessage();
        $result = $event->getResult();

        // 记录到splunk中
        Splunk::log('wechat_message_send', [
            'status' => $result->isSuccess() ? 'success' : 'fail',
            'message' => $message->parse(),
            'msg_id' => $message->getId(),
            'user_id' => $message->getUserId(),
            'third_id' => $message->getThirdId(),
            'result_error' => $result->error,
            'result_result' => $result->response,
        ]);

        // 删除缓存中的数据
        MessageBody::del($message->getId());
        MessageMinute::del($message->getId());
    }
}
