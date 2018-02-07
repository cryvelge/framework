<?php

namespace App\Components\Storage;

use App\Components\Storage\Exceptions\AllStorageFailedException;
use App\Components\Storage\Models\File;
use App\Components\Storage\Stores\Aliyun;
use App\Components\Storage\Stores\Qiniu;
use App\Library\Exceptions\FatalError;
use App\Library\SingletonTrait;
use Illuminate\Support\Collection;

/**
 * Class Manager
 * @package App\Components\Storage
 */
class Manager
{
    use SingletonTrait;

    /**
     * @var IStoreDriver
     */
    private $driver;

    /**
     * Manager constructor.
     *
     * @throws FatalError
     */
    private function __construct()
    {
        $this->driver = static::getStore(config('storage.main_driver'));
    }

    /**
     * @param int $id
     * @param bool $internal
     * @return string
     * @throws
     */
    public function getFileUrl(int $id, bool $internal = false)
    {
        $file = File::find($id);
        return $this->getUrl($file, $internal);
    }

    /**
     * @param array $ids
     * @param bool $internal
     * @return Collection
     */
    public function batchGetFileUrl(array $ids, bool $internal = false)
    {
        return File::whereIn('id', $ids)->get()->keyBy('id')->map(function($file) use ($internal) {
            return $this->getUrl($file, $internal);
        });
    }

    /**
     * @param string $key
     * @param string $type
     * @param string $path
     * @return File
     */
    public function storeFile(string $key, string $type, string $path)
    {
        $this->driver->uploadFile($path, $key);
        return $this->createFile($key, $type);
    }

    /**
     * @param string $key
     * @param string $type
     * @param string $content
     * @return File
     */
    public function storeBinary(string $key, string $type, string $content)
    {
        $this->driver->uploadBinary($content, $key);
        return $this->createFile($key, $type);
    }

    /**
     * @param File $file
     * @param bool $internal
     * @return string
     * @throws FatalError
     */
    public function getUrl(File $file, bool $internal = false)
    {
        if ($internal) {
            $method = 'getUrlInternal';
        } else {
            $method = 'getUrl';
        }

        $driver = static::getStore($file->store);

        return $driver->{$method}($file);
    }

    /**
     * @param $key
     * @param $type
     * @return File
     */
    private function createFile($key, $type)
    {
        $data = [
            'key' => $key,
            'type' => $type,
            'store' => $this->driver->getName(),
        ];

        return File::create($data);
    }

    /**
     * @param string $store
     * @return IStoreDriver
     * @throws FatalError
     */
    private static function getStore(string $store)
    {
        switch ($store) {
            case 'qiniu':
                return Qiniu::instance();
            case 'aliyun':
                return Aliyun::instance();
            default:
                throw new FatalError('file store not fount');
        }
    }
}
