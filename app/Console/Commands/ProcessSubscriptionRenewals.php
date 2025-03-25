<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ProcessSubscriptionRenewals extends Command
{
    protected $signature = 'subscriptions:process-renewals';
    protected $description = 'Process subscription renewals and point rollovers';

    public function handle()
    {
        $expiredUsers = User::where('subscription_expires_at', '<=', now())
            ->where('points_to_rollover', '>', 0)
            ->get();

        foreach ($expiredUsers as $user) {
            // Store points to rollover before they expire
            $pointsToRollover = $user->points;
            
            // Reset points to 0 since subscription expired
            $user->points = 0;
            $user->points_to_rollover = $pointsToRollover;
            $user->save();
            
            // Notify user about expiration and points on hold
            $user->notify(new \App\Notifications\SubscriptionExpired([
                'points_on_hold' => $pointsToRollover
            ]));
        }

        $this->info("Processed {$expiredUsers->count()} expired subscriptions.");
    }
}
