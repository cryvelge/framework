<?php

namespace App\Components\WeChatCode\Handlers;

use App\Components\WeChatCode\Models\WeChatCode;

/**
 * Interface IHandler
 * @package App\Components\WeChatCode\Handlers
 */
interface IHandler
{
    /**
     * @param int $userId
     * @param WeChatCode $code
     * @param bool $subscribe
     * @return void
     */
    public static function handle(int $userId, WeChatCode $code, bool $subscribe): void;
}
