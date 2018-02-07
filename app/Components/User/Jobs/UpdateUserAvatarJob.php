<?php

namespace App\Components\User\Jobs;

use App\Components\User\Models\Client;
use App\Components\User\Models\User;
use Carbon\Carbon;
use EasyWeChat;
use ExceptionNotifier\Notifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateUserAvatarJob implements ShouldQueue
{
    use Queueable;

    private $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
        $this->delay(5);
    }

    public function handle()
    {
        $userService = EasyWeChat::user();
        $user = User::find($this->userId);
        $client = Client::where('user_id', $this->userId)->first();

        try {
            $userInfo = $userService->get($client->open_id);
            if ($userInfo->subscribe) {
                $user->updateUserInfo([
                    'avatar' => $userInfo->headimgurl,
                    'nickname' => $userInfo->nickname,
                ]);
            }
        } catch (\Throwable $e) {
            Notifier::notify($e);
        } finally {
            $user->update([
                'last_update_at' => Carbon::now()->toDateTimeString()
            ]);
        }
    }
}
