<?php

namespace App\Components\Storage\Stores;

use App\Components\Storage\IStoreDriver;
use App\Components\Storage\Models\File;
use App\Library\SingletonTrait;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

/**
 * Class Qiniu
 * @package App\Components\Storage\Stores
 */
class Qiniu implements IStoreDriver
{
    use SingletonTrait;

    private $accessKey;
    private $secretKey;
    private $auth;
    private $bucket;
    private $host;
    private $ssl;
    private $schema;

    private function __construct()
    {
        $this->accessKey = config('storage.qiniu.access_key');
        $this->secretKey = config('storage.qiniu.secret_key');
        $this->auth = new Auth($this->accessKey, $this->secretKey);
        $this->bucket = config('storage.qiniu.bucket');
        $this->host = config('storage.qiniu.host');
        $this->ssl = config('storage.qiniu.ssl');
        $this->schema = $this->ssl ? 'https' : 'http';
    }

    /**
     * @param string $path
     * @param string $key
     * @return string
     * @throws \Exception
     */
    public function uploadFile(string $path, string $key): string
    {
        $token = $this->auth->uploadToken($this->bucket);
        $uploadManager = new UploadManager();
        list($ret, $err) = $uploadManager->putFile($token, $key, $path);
        if ($err !== null) {
            throw new \Exception($err->message(), $err->code());
        } else {
            return $this->host . $ret['key'];
        }
    }

    /**
     * @param $data
     * @param string $key
     * @return string
     * @throws \Exception
     */
    public function uploadBinary(string $data, string $key): string
    {
        $token = $this->auth->uploadToken($this->bucket);
        $uploadManager = new UploadManager();
        list($ret, $err) = $uploadManager->put($token, $key, $data);
        if ($err !== null) {
            throw new \Exception($err->message(), $err->code());
        } else {
            return $this->host . $ret['key'];
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'qiniu';
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
        return $this->getUrl($file);
    }
}
