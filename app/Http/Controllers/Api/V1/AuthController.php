<?php

namespace App\Http\Controllers\Api\V1;

use App\Library\WXApp\User as WXAppUser;
use App\Components\User\Manager as UserManager;
use Illuminate\Http\Request;

class AuthController extends ApiController
{
    public function login(Request $request)
    {
        $code = $request->input('code');
        $rawData = $request->input('raw_data');
        $encryptedData = $request->input('encrypted_data');
        $iv = $request->input('iv');
        $signature = $request->input('signature');

        list($openId, $sessionKey) = WXAppUser::instance()->getUserSessionKey($code);
        $userInfo = WXAppUser::instance()->decryptUserInfo($rawData, $encryptedData, $iv, $signature);
        UserManager::instance()->loginWXApp($openId, $sessionKey, $userInfo);

        return $this->success();
    }
}
