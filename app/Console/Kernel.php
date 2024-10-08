<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\DailyAttendance;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('daily-attendance')->dailyAt('18:38')->timezone('Asia/Jakarta');
        $schedule->command(command: 'daily-attendance')
            ->weekdays()
            ->dailyAt('04:00')
            ->timezone('Asia/Jakarta');
    }

    // protected $commands = [
    //     Commands\DailyAttendance::class,
    // ];

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}