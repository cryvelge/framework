<?php

namespace App\Components\MessageHandler;

use App\Components\MessageHandler\Handlers\EventHandler;
use App\Components\MessageHandler\Handlers\TextHandler;
use App\Components\User\Manager as UserManager;
use EasyWeChat\Support\Collection;
use Splunk;

class Manager
{
    public static function handle(Collection $message)
    {
        $data = $message->toArray();

        if ($message->MsgType != 'event' || ($message->Event != 'subscribe' && $message->Event != 'unsubscribe')) {
            $user = UserManager::getByOpenId('wechat', $message->FromUserName);
            if (is_null($user)) {
                $client = UserManager::registerBySubscribe($message->FromUserName);
                $user = $client->user();
            }

            $client = $user->getClient();

            if ($client->subscribe == 0) {
                $client->update([
                    'subscribe' => 1
                ]);
            }

            $message->user = $user;

            $data['user_id'] = $user->id;
        }

        Splunk::log('we_chat_message', $data);

        switch ($message->MsgType) {
            case 'event':
                return EventHandler::handle($message);
            case 'text':
                return TextHandler::handle($message);
            case 'image':
                return null;
            case 'voice':
                return null;
            case 'video':
                return null;
            case 'location':
                return null;
            case 'link':
                return null;
            default:
                return null;
        }
    }
}
