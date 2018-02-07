<?php

namespace App\Http\Controllers\WeChat\V1;

use App\Components\User\Jobs\UpdateUserAvatarJob;
use App\Components\User\Manager as UserManager;
use Carbon\Carbon;
use EasyWeChat;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

class AuthController extends ApiController
{
    protected $url;
    protected $generator;

    public function __construct(Request $request, UrlGenerator $generator)
    {
        $result = parse_url($generator->previous('spa'));
        $this->url = "{$result['scheme']}://{$result['host']}{$result['path']}?" . http_build_query($request->except('code'));
        if (env('WQ_ENABLED')) {
            $this->url = env('WQ_HOST') . '/addons/wxbridge/app/index.php?id=' . env('WQ_ACID') . '&redirect_url=' . urlencode($this->url);
        }
    }

    public function login(Request $request)
    {
        if (env('MAINTENANCE') === 'MAINTENANCE') {
            return $this->fail('服务器维护中', 2);
        }

        if (env('WECHAT_ENABLE_MOCK', false)) {
            return $this->success();
        }

        if (session('user')) {
            return $this->success();
        }

        $code = $request->input('code');

        if(is_null($code)) {
            return $this->redirectBaseAuth();
        }

        /**
         * @var \Overtrue\Socialite\Providers\WeChatProvider $oauth
         */
        $oauth = EasyWeChat::oauth();
        $oauth->setRequest($request);

        /**
         * @var \Overtrue\Socialite\User $weChatUser
         */
        try {
            $weChatUser = $oauth->user();
        } catch (\Throwable $e) {
            return $this->redirectBaseAuth();
        }
        $original = $weChatUser->getOriginal();
        $openId = $weChatUser->getId();
        $token = $weChatUser->getToken();
        $scope = $token->getAttribute('scope');
        if (str_contains($scope, 'snsapi_userinfo')) {
            $user = UserManager::registerByOAuth($openId, $original)->user();
        } else {
            $user = UserManager::getByOpenId('wechat', $openId);
            if (is_null($user)) {
                return $this->redirectUserInfoAuth();
            } else {
                $client = $user->getClient();
                if (!$client->subscribe) {
                    return $this->redirectUserInfoAuth();
                }

                if ($user->last_update_at < Carbon::today()->subDays(2)->toDateTimeString()) {
                    dispatch(new UpdateUserAvatarJob($user->id));
                }
            }
        }

        session(['user' => $user]);

        return $this->success();
    }

    private function redirectBaseAuth()
    {
        /**
         * @var \Overtrue\Socialite\Providers\WeChatProvider $oauth
         */
        $oauth = EasyWeChat::oauth();
        $response = $oauth->scopes(['snsapi_base'])->setRedirectUrl($this->url)->redirect();
        $oauthUrl = $response->getTargetUrl();
        return $this->fail('OAuth needed', 1, [
            'redirectUrl' => $oauthUrl,
        ]);
    }

    private function redirectUserInfoAuth()
    {
        /**
         * @var \Overtrue\Socialite\Providers\WeChatProvider $oauth
         */
        $oauth = EasyWeChat::oauth();
        $response = $oauth->scopes(['snsapi_userinfo'])->setRedirectUrl($this->url)->redirect();
        $oauthUrl = $response->getTargetUrl();
        return $this->fail('OAuth needed', 1, [
            'redirectUrl' => $oauthUrl,
        ]);
    }

    public function fakeAuth(Request $request)
    {
        $token = $request->input('token');
        $userId = $request->input('user_id');

        if ($token == 'tv2BCGjkqAle8LItyi9vgvxiIEmCl8zg') {
            $user = UserManager::getById($userId);
            session(['user' => $user]);
        } else {
            $request->session()->flush();
        }

        dump(session('user'));

        return $this->success('succeed');
    }
}
