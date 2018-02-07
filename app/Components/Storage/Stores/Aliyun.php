<?php

namespace App\Components\Storage\Stores;

use App\Components\Storage\IStoreDriver;
use App\Components\Storage\Models\File;
use App\Library\SingletonTrait;
use OSS\OssClient;

/**
 * Class Aliyun
 * @package App\Components\Storage\Stores
 */
class Aliyun implements IStoreDriver
{
    use SingletonTrait;

    private $accessKeyId;
    private $accessKeySecret;
    private $endpoint;
    private $bucket;
    private $host;
    private $internalHost;
    private $client;
    private $ssl;
    private $schema;

    private function __construct()
    {
        $this->accessKeyId = config('storage.aliyun.access_key_id');
        $this->accessKeySecret = config('storage.aliyun.access_key_secret');
        $this->endpoint = config('storage.aliyun.endpoint');
        $this->bucket = config('storage.aliyun.bucket');
        $this->host = config('storage.aliyun.host');
        $this->internalHost = config('storage.aliyun.internal_host');
        $this->client = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
        $this->ssl = config('storage.aliyun.ssl');
        $this->schema = $this->ssl ? 'https' : 'http';
    }

    /**
     * @param string $path
     * @param string $key
     * @return string
     * @throws
     */
    public function uploadFile(string $path, string $key): string
    {
        $this->client->uploadFile($this->bucket, $key, $path);
        return $this->host . $key;
    }

    /**
     * @param string $data
     * @param string $key
     * @return string
     */
    public function uploadBinary(string $data, string $key): string
    {
        $this->client->putObject($this->bucket, $key, $data);
        return $this->host . $key;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'aliyun';
    }

    /**
     * @param File $file
     * @return string
     */
    public function getUrl(File $file): string
    {
        return implode('', [
            $this->schema,
            '://',
            $this->host,
            '/',
            $file->key
        ]);
    }

    /**
     * @param File $file
     * @return string
     */
    public function getUrlInternal(File $file): string
    {
        if (is_null($this->internalHost)) {
            return $this->getUrl($file);
        } else {
            return implode('', [
                'http://',
                $this->internalHost,
                '/',
                $file->key
            ]);
        }
    }
}
