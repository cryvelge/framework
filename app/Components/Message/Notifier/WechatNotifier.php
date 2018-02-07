<?php

namespace App\Components\Message\Notifier;

use App\Components\Message\Message;
use App\Components\Message\MessageSendResult;
use EasyWeChat\Core\Exceptions\HttpException;
use EasyWeChat\Message\Text;
use EasyWeChat\Message\Image;
use EasyWeChat\Message\Voice;
use EasyWeChat\Message\News;
use EasyWeChat\Message\Material;

use App\Components\User\Manager as UserManager;

// 内部
use App\Components\Message\INotifier;
// Exceptions
use App\Components\Message\Exceptions\MessageIncompleteDataException;
use App\Components\Message\Exceptions\MessageReceiverNotFoundException;
use EasyWeChat;

/**
 * 微信消息Notifier
 *
 * @package App\Components\Message\Notifier
 */
class WechatNotifier implements INotifier {

    /**
     * 发送消息对象
     *
     * @param Message $message
     *
     * @return MessageSendResult
     *
     * @throws MessageIncompleteDataException
     * @throws MessageReceiverNotFoundException
     */
    public function send($message) : MessageSendResult
    {
        if ($message->type == null) {
            throw new MessageIncompleteDataException($message->getId(), 'type');
        }
        if ($message->content == null) {
            throw new MessageIncompleteDataException($message->getId(), 'content');
        }

        // 确认thirdId
        $third_id = $message->getThirdId();
        if ($third_id == null) {
            $third_id = $this->getThirdId($message->getUserId());
        }
        if ($third_id == null) {
            throw new MessageReceiverNotFoundException($message->getId(), $message->getUserId(), $message->getThirdId());
        }
        $message->setThirdId($third_id);

        switch($message->type) {
            case 'text':
                $wechat_message = new Text(['content' => $message->content]);
                return $this->sendMessage($wechat_message, $message);
                break;
            case 'image':
                $wechat_message = new Image(['media_id' => $message->content]);
                return $this->sendMessage($wechat_message, $message);
                break;
            case 'voice':
                $wechat_message = new Voice(['media_id' => $message->content]);
                return $this->sendMessage($wechat_message, $message);
                break;
            case 'news':
                $wechat_message = [];
                for ($i = 0, $len = count($message->content); $i < $len; $i++) {
                    $news = $message->content[$i];
                    $wechat_message[] = new News([
                        'title' => $news['title'],
                        'description' => $news['description'] ?? null,
                        'url' => $news['url'] ?? null,
                        'image' => $news['picurl'] ?? null,
                    ]);
                }
                return $this->sendMessage($wechat_message, $message);
                break;
            case 'mpnews':
                $wechat_message = new Material('mpnews', $message->content);
                return $this->sendMessage($wechat_message, $message);
                break;
            case 'template':
                return $this->sendTemplateMessage($message);
                break;
            default:
                // TODO: 无效的type类型
        }
    }

    /**
     * 发送普通消息
     *
     * @param Message $message
     */
    public function sendMessage($wechat_message, $message) : MessageSendResult
    {
        // 确认发送的结果
        $result = new MessageSendResult();
        try {
            $result->result = 0;
            if (is_null($message->staff)) {
                $result->response = EasyWeChat::staff()
                    ->message($wechat_message)
                    ->to($message->getThirdId())
                    ->send()
                    ->toArray();
            } else {
                $result->response = EasyWeChat::staff()
                    ->message($wechat_message)
                    ->by($message->staff)
                    ->to($message->getThirdId())
                    ->send()
                    ->toArray();
            }
        } catch(HttpException $e) {
            $result->result = 1;
            $result->response = $e->getMessage();
        }
        return $result;
    }

    /**
     * 发送模板消息
     *
     * @param Message $message
     *
     * @return MessageSendResult
     */
    public function sendTemplateMessage(Message $message) : MessageSendResult
    {
        $result = new MessageSendResult();
        try {

            $templateId = config("wechat.template_id.{$message->templateId}");
            if ($templateId == null) {
                $templateId = $message->templateId;
            }

            $result->result = 0;
            $result->response = EasyWeChat::notice()->send([
                'touser' => $message->getThirdId(),
                'template_id' => $templateId,
                'url' => $message->url,
                'data' => $message->content,
                'miniprogram' => $message->miniprogram,
            ])->toArray();
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
        $openid = UserManager::getOpenid($user_id, 'wechat');
        return $openid;
    }
}