<?php

namespace App\Components\Storage;

use Illuminate\Console\Scheduling\Schedule as ConsoleSchedule;

class Schedule
{
    public static function invoke(ConsoleSchedule $schedule)
    {
        $schedule->command('storage:sync')->everyMinute()->name('storage')->withoutOverlapping();
    }
}
