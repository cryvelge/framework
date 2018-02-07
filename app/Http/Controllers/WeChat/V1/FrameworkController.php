<?php

namespace App\Http\Controllers\WeChat\V1;

use App\Components\Storage\Manager as FileManager;
use Carbon\Carbon;
use EasyWeChat\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Ramsey\Uuid\Uuid;

class FrameworkController extends ApiController
{
    public function getJsSdkConfig(UrlGenerator $generator, Application $easyWeChat)
    {
        $url = $generator->previous();
        $signature = $easyWeChat->js->signature($url);
        return $this->success($signature);
    }

    public function uploadImage(Request $request, Application $easyWeChat)
    {
        $mediaId = $request->input('media_id');
        $type = $request->input('type');

        $user = session('user');
        $date = Carbon::today()->toDateString();
        $uuid = Uuid::uuid4()->getHex();

        $content = $easyWeChat->material_temporary->getStream($mediaId);
        $file = FileManager::instance()->storeBinary("{$type}/{$date}/{$user->unique_id}/{$uuid}", $type, $content);

        return $this->success($file->id);
    }
}
