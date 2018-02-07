<?php

namespace App\Components\Message\Notifier;

use App\Components\Message\Exceptions\MessageReceiverNotFoundException;
use App\Components\Message\INotifier;
use App\Components\Message\MessageSendResult;
use EasyWeChat;
use EasyWeChat\Core\Exceptions\HttpException;
use App\Components\User\Manager as UserManager;

/**
 * 允许使用的模板消息id有
 *  - participate_success
 */
class MiniProgramNotifier implements INotifier {

    /**
     * 发送消息
     * 因为默认存储使用了data，所以将所有的data内容换成了content
     *
     * @param \App\Components\Message\Message $message
     * @return MessageSendResult
     */
    public function send($message): MessageSendResult
    {
        $thirdId = $message->getThirdId();
        if ($thirdId == null) {
            $thirdId = $message->setThirdId($this->getThirdId($message->getUserId()));
        }
        if ($thirdId == null) {
            throw new MessageReceiverNotFoundException($message->getId(), $message->getUserId(), $message->getThirdId());
        }

        $result = new MessageSendResult();
        try {
            $result->result = 0;

            $templateId = config("wechat.template_id.{$message->templateId}");
            if ($templateId == null) {
                $templateId = $message->templateId;
            }
            $raw = [
                'touser' => $message->getThirdId(),
                'template_id' => $templateId,
                'form_id' => $message->formId,
                'data' => $message->content,
                'emphasis_keyword' => $message->emphasisKeyword,
            ];
            if ($message->page != null) {
                $raw['page'] = $message->page;
            }
            $result->response = EasyWeChat::mini_program()->notice->send($raw)->toArray();
        } catch(HttpException $e) {
            $result->result = 1;
            $result->response = [
                'errcode' => $e->getCode(),
                'errmsg' => $e->getMessage(),
            ];
        }
        return $result;
    }

    /**
     * 获得thirdId
     * @param $user_id
     */
    public function getThirdId($user_id) {
        $openid = UserManager::getOpenid($user_id, 'miniprogram');
        return $openid;
    }
}