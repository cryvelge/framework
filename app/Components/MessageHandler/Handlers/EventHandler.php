<?php

namespace App\Components\MessageHandler\Handlers;

use App\Components\Activity\AnnualRun\Manager as AnnualRunManager;
use App\Components\Activity\AnnualRun\Models\Participation;
use App\Components\MessageHandler\Cache\AnnualRunSubscribeMessageCache;
use App\Components\MessageHandler\Cache\SignInMessageCache;
use App\Components\User\Manager as UserManager;
use App\Components\WeChatCode\Manager as WeChatCodeManager;
use App\Library\FrontendUrl;
use Carbon\Carbon;
use EasyWeChat\Support\Collection;
use Log;
use Message;

class EventHandler
{
    public static function handle(Collection $message)
    {
        switch ($message->Event) {
            case 'subscribe':
                UserManager::registerBySubscribe($message->FromUserName, true);

                $text = "欢迎关注";
                return $text;
            case 'unsubscribe':
                UserManager::unSubscribe($message->FromUserName);
                return null;
            case 'SCAN':
                $scene = static::extractScene($message->EventKey);
                WeChatCodeManager::handle($message->user->id, $scene, false);
                return null;
            case 'CLICK':
                return null;
            default:
                return null;
        }
    }

    private static function extractScene($eventKey)
    {
        if (strpos($eventKey, 'qrscene_') === 0) {
            return substr($eventKey, 8);
        } else {
            return $eventKey;
        }
    }
}
