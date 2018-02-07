<?php

namespace App\Components\WeChatCode\Cache;

use App\Components\WeChatCode\Models\WeChatCode;
use LaravelRedis;

/**
 * Class CodeCache
 * @package App\Components\WeChatCode\Cache
 */
class CodeCache
{
    /**
     * @param string $scene
     * @return WeChatCode
     */
    public static function get(string $scene)
    {
        $record = LaravelRedis::get("cache:we_chat_code:{$scene}");

        if (is_null($record)) {
            $record = WeChatCode::where('scene', $scene)->first();
            LaravelRedis::setEx("cache:we_chat_code:{$scene}", 600, serialize($record));
        } else {
            $record = unserialize($record);
        }

        return $record;
    }
}
