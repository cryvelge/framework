<?php

namespace App\Components\WeChatCode\Handlers;

use App\Components\WeChatCode\Models\WeChatCode;
use Message;

/**
 * Class SampleHandler
 * @package App\Components\WeChatCode\Handlers
 */
class SampleHandler implements IHandler
{
    /**
     * @param int $userId
     * @param WeChatCode $code
     * @param bool $subscribe
     */
    public static function handle(int $userId, WeChatCode $code, bool $subscribe): void
    {
        $message = Message::generate([
            'type' => 'text',
            'content' => "你扫描了二维码, 场景值为{$code->scene}",
        ]);
        $message->setTopic('wechat');
        $message->setUserId($userId);
        $message->send();
    }
}
