<?php

namespace App\Components\Message\Exceptions;

use App\Library\Exceptions\FatalError;

class MessageIncompleteDataException extends FatalError
{
    public function __construct($id, $name)
    {
        parent::__construct("Message data is incomplete. MessageId: {$id}, Value Name: {$name}");
    }

    public function getExceptionLiteral() : string
    {
        return "消息数据不完整";
    }
}
