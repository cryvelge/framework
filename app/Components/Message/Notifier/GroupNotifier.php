<?php

namespace App\Components\Message\Notifier;

use App\Components\Message\MessageSendResult;

use App\Components\Message\INotifier;
use App\Components\Message\Message;
use App\Components\Message\Manager;

class GroupNotifier implements INotifier {

	/**
	 * 发送消息
	 *
	 * @param MessageGroup $message 消息对象，一个MessageGroup
	 * 
	 * @return MessageSendResult 返回消息发送的结果，只有在全部发送成功情况下，才会是成功，没有response和error信息
	 */
    public function send($message) : MessageSendResult
    {
    	$manager = Manager::instance();
        $messages = $message->getData();
        $flag = true;
        for ($i = 0, $len = count($messages); $i < $len; $i++) {
            $send_result = $manager->sendSync($messages[$i]);
            $flag |= $send_result->isSuccess();
        }
        $result = new MessageSendResult();
        if ($flag) {
        	$result->result = 0; // 全部发送成功
        } else {
        	$result->result = 1;
        }
        return $result;
    }
}