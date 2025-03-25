<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Process expired subscriptions daily at midnight
        $schedule->command('subscriptions:process-renewals')
            ->dailyAt('00:00');

        // Send subscription expiration reminders daily at 9 AM
        $schedule->command('subscriptions:send-reminders')
            ->dailyAt('09:00');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
