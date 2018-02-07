<?php

namespace App\Components\User\Cache;

use App\Components\User\Exceptions\ClientNotFoundException;
use App\Components\User\Models\Client;
use LaravelRedis;

/**
 * Class OpenIdCache
 * @package App\Components\User\Cache
 */
class OpenIdCache
{
    /**
     * Cache key
     */
    public const KEY = 'user:open_id';

    /**
     * Cache timeout
     */
    protected const TIMEOUT = 300;

    /**
     * 获取公众号或小程序缓存
     *  wechat -- 公众号
     *  miniprogram -- 小程序
     * @param string $type
     * @return static
     */
    public static function type(string $type = 'wechat')
    {
        return new static($type);
    }

    /**
     * 缓存类型
     * @var string
     */
    private $type;

    /**
     * Cache key
     * @var string
     */
    private $key;

    /**
     * OpenIdCache constructor.
     * @param string $type
     */
    private function __construct(string $type)
    {
        $this->type = $type;
        $this->key = static::KEY . ':' . $type;
    }

    /**
     * Get open_id by user_id
     * @param int $userId
     * @return string|null
     */
    public function get(int $userId) : ?string
    {
        $key = "{$this->key}:{$userId}";
        $openId = LaravelRedis::get($key);

        if (is_null($openId)) {
            $client = Client::where('user_id', $userId)->where('type', $this->type)->first();
            if (is_null($client)) {
                return null;
            }
            $openId = $client->open_id;
            LaravelRedis::set($key, $openId, static::TIMEOUT);
        }

        return $openId;
    }

    /**
     * Get multiple open_ids at a time
     * @param array $userIds
     * @return array
     */
    public function multiGet(array $userIds)
    {
        $results = LaravelRedis::pipeline(function(\Redis $pipe) use ($userIds) {
            foreach ($userIds as $id) {
                $pipe->get("{$this->key}:{$id}");
            }
        });

        $emptyCache = [];

        foreach ($results as $index => $row) {
            if ($row === false) {
                $emptyCache[$userIds[$index]] = $index;
            }
        }

        $ids = array_keys($emptyCache);

        $clients = Client::whereIn('user_id', $ids)->where('type', $this->type)->get();

        $clients->each(function($client) use (&$emptyCache, &$results) {
            $results[$emptyCache[$client->user_id]] = $client->open_id;
        });

        LaravelRedis::pipeline(function(\Redis $pipe) use ($clients) {
            foreach ($clients as $client) {
                $pipe->set("{$this->key}:{$client->user_id}", $client->open_id, static::TIMEOUT);
            }
        });

        return $results;
    }
}
