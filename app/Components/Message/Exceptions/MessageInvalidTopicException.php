<?php

namespace App\Components\Message\Exceptions;

use App\Library\Exceptions\FatalError;

class MessageInvalidTopicException extends FatalError
{
    public function __construct($id, $topic)
    {
        parent::__construct("Message topic invalid. MessageId: {$id}, Topic: {$topic}");
    }

    public function getExceptionLiteral() : string
    {
        return "无效的话题类型";
    }
}
