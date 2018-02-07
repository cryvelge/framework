<?php

namespace App\Components\Message\Schedule;

use Carbon\Carbon;
use Log;

use App\Components\Message\Scheduler;

class Schedule {

    public static function schedule($schedule) {
        $schedule->call(function() {
            $timestamp = Carbon::now()->timestamp;
            $minute = (int)($timestamp / 60);
            
            // 发送消息
            Scheduler::sendScheduleMessage($minute);
        })->everyMinute();
    }
}