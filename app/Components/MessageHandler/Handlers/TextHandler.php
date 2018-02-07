<?php

namespace App\Components\MessageHandler\Handlers;

use App\Components\Activity\HundredDay5\Manager as HundredDay5Manager;
use App\Components\Activity\HundredDay6\Manager as HundredDay6Manager;
use App\Components\Card2\Material;
use App\Components\Card2\Type\HundredDay6Certificate;
use App\Components\MessageHandler\Job\SignInJob;
use App\Components\MessageHandler\Cache\SignInMessageCache;
use App\Components\Team\Models\Participation;
use App\Components\User\Manager as UserManager;
use App\Components\WeChatCode\Manager as WeChatCodeManager;
use App\Library\FrontendUrl;
use Cache;
use Carbon\Carbon;
use EasyWeChat;
use EasyWeChat\Message\Image;
use EasyWeChat\Message\News;
use EasyWeChat\Message\Text;
use EasyWeChat\Support\Collection;
use ExceptionNotifier\Notifier;
use Log;

class TextHandler
{
    public static function handle(Collection $message)
    {
        $ret = static::matchPhrases($message);

        if (!$ret) {
            $ret = static::matchKeywords($message->Content);
        }

        return $ret;
    }

    private static function matchPhrases(Collection $message)
    {
        switch($message->Content) {
            case '完全匹配':
                return '完全匹配';
        }
    }

    private static function matchKeywords(string $text) {
        if (preg_match('/部分|匹配/', $text)) {
            return '部分匹配';
        }

        return null;
    }
}
