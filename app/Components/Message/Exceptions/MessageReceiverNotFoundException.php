<?php

namespace App\Components\Message\Exceptions;

use App\Library\Exceptions\FatalError;

class MessageReceiverNotFoundException extends FatalError
{
    public function __construct($id, $user_id, $third_id)
    {
        parent::__construct("Message receiver cannot be found. MessageId: {$id} UserId: {$user_id} ThirdId: ${third_id}");
    }

    public function getExceptionLiteral() : string
    {
        return "消息接收者无法找到";
    }
}
