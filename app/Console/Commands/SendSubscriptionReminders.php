<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\SubscriptionExpiringSoon;
use Illuminate\Console\Command;

class SendSubscriptionReminders extends Command
{
    protected $signature = 'subscriptions:send-reminders';
    protected $description = 'Send reminders to users whose subscriptions are expiring soon';

    public function handle()
    {
        // Notify users 7 days before expiration
        $users = User::where('subscription_expires_at', '>', now())
            ->where('subscription_expires_at', '<=', now()->addDays(7))
            ->get();

        foreach ($users as $user) {
            $daysLeft = now()->diffInDays($user->subscription_expires_at);
            $user->notify(new SubscriptionExpiringSoon($daysLeft));
        }

        $this->info("Sent reminders to {$users->count()} users.");
    }
}
