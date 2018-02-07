<?php

namespace App\Components\Message\Exceptions;

use App\Library\Exceptions\FatalError;

/**
 * 重复注册的topic
 */
class MessageDuplicateTopicException extends FatalError
{
    public function __construct($id, $topic)
    {
        parent::__construct("Topic already register. Topic: {$topic}");
    }

    public function getExceptionLiteral() : string
    {
        return "话题类型重复注册";
    }
}
