<?php

namespace App\Components\WeChatCode;

use App\Components\WeChatCode\Cache\CodeCache;
use App\Components\WeChatCode\Handlers\GeneralHandler;
use App\Components\WeChatCode\Handlers\IHandler;
use App\Components\WeChatCode\Handlers\SampleHandler;
use App\Components\WeChatCode\Models\ScanWeChatCode;
use App\Components\WeChatCode\Models\WeChatCode;
use Carbon\Carbon;
use EasyWeChat;
use Ramsey\Uuid\Uuid;

/**
 * Class Manager
 * @package App\Components\QrCode
 */
class Manager
{
    const SAMPLE_CODE = 'sample_code';

    const HANDLER_MAP = [
        'sample_code' => SampleHandler::class,
    ];

    const TYPE_MAP = [
        'sample_code' => '示例',
    ];

    /**
     * @param int $userId
     * @param string $type
     * @param string $description
     * @param null $data
     * @return WeChatCode
     */
    public static function temporary(int $userId, string $type, string $description, $data = null)
    {
        $uuid = Uuid::uuid4()->toString();
        $scene = "{$type}:{$userId}:{$uuid}";
        $result = EasyWeChat::qrcode()->temporary($scene, 2592000);
        return WeChatCode::create([
            'user_id' => $userId,
            'type' => $type,
            'handler' => static::HANDLER_MAP[$type],
            'description' => $description,
            'scene' => $scene,
            'data' => $data,
            'ticket' => $result->ticket,
            'url' => $result->url,
            'expire_at' => Carbon::now()->addSeconds(2592000)->toDateTimeString(),
        ]);
    }

    /**
     * @param int $userId
     * @param string $type
     * @param string $description
     * @param null $data
     * @return WeChatCode
     */
    public static function permanent(int $userId, string $type, string $description, $data = null)
    {
        $uuid = Uuid::uuid4()->toString();
        $scene = "{$type}:{$userId}:{$uuid}";
        $result = EasyWeChat::qrcode()->forever($scene);
        return WeChatCode::create([
            'user_id' => $userId,
            'type' => $type,
            'handler' => static::HANDLER_MAP[$type],
            'description' => $description,
            'scene' => $scene,
            'data' => $data,
            'ticket' => $result->ticket,
            'url' => $result->url,
        ]);
    }

    /**
     * @param int $userId
     * @param string $scene
     * @param bool $subscribe
     */
    public static function handle(int $userId, string $scene, bool $subscribe = false)
    {
        $code = CodeCache::get($scene);

        ScanWeChatCode::create([
            'user_id' => $userId,
            'we_chat_code_id' => $code->id,
            'subscribe' => $subscribe,
        ]);

        /**
         * @var IHandler $handler
         */
        $handler = $code->handler;

        $handler::handle($userId, $code, $subscribe);

        if (method_exists($handler, 'handleInvite')) {
            $handler::handleInvite($userId, $code, $subscribe);
        }
    }
}
