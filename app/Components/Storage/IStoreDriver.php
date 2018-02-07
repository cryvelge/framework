<?php

namespace App\Components\Storage;

use App\Components\Storage\Models\File;

interface IStoreDriver
{
    public function uploadFile(string $path, string $key): string;
    public function uploadBinary(string $data, string $key): string;
    public function getName(): string;
    public function getUrl(File $file): string;
    public function getUrlInternal(File $file): string;
}