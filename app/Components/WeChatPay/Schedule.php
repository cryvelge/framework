<?php

namespace App\Components\WeChatPay;

use App\Components\WeChatPay\Models\WeChatCompanyPay;
use App\Components\WeChatPay\Models\WeChatRedPack;
use App\Components\WeChatPay\Models\WeChatRefund;
use Illuminate\Console\Scheduling\Schedule as ConsoleSchedule;

class Schedule
{
    public static function invoke(ConsoleSchedule $schedule)
    {
        $schedule->call(function() {
            $records = WeChatRefund::where('status', WeChatRefund::STATUS_PROCESSING)->get();
            $records->each->checkStatus();

            $records = WeChatCompanyPay::where('status', WeChatCompanyPay::STATUS_SENDING)->get();
            $records->each->checkStatus();

            $records = WeChatRedPack::whereIn('status', [
                WeChatRedPack::STATUS_SENDING,
                WeChatRedPack::STATUS_REFUND_ING,
                WeChatRedPack::STATUS_SENT,
            ])->get();
            $records->each->checkStatus();
        })->everyFiveMinutes()->name('wechat_pay_check');
        $schedule->call(function() {
            $records = WeChatRefund::where('status', WeChatRefund::STATUS_INITIALIZED)->get();
            $records->each->execute();

            $records = WeChatCompanyPay::where('status', WeChatCompanyPay::STATUS_PENDING_CHECK)->get();
            $records->each->execute();

            $records = WeChatRedPack::where('status', WeChatRedPack::STATUS_PENDING_CHECK)->get();
            $records->each->execute();
        })->everyFiveMinutes()->name('wechat_pay_execute');
    }
}
