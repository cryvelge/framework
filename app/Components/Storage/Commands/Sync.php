<?php

namespace App\Components\Storage\Commands;

use App\Components\Storage\Manager;
use App\Components\Storage\Models\File;
use App\Components\Storage\Stores\Aliyun;
use App\Components\Storage\Stores\Qiniu;
use Illuminate\Console\Command;

class Sync extends Command
{
    protected $signature = 'storage:sync';

    protected $description = 'Sync between storage drivers';

    public function handle()
    {
        File::where('aliyun', 0)->orWhere('qiniu', 0)->get()->each(function($row) {
            try {
                $url = Manager::instance()->getUrl($row);
                $content = file_get_contents($url);
                if (!$row->aliyun) {
                    Aliyun::instance()->uploadBinary($content, $row->key);
                    $row->update(['aliyun' => 1]);
                } else {
                    Qiniu::instance()->uploadBinary($content, $row->key);
                    $row->update(['qiniu' => 1]);
                }
            } catch (\Throwable $e) {
                throw $e;
            }
        });
    }
}
