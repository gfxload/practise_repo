<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringSoon extends Notification implements ShouldQueue
{
    use Queueable;

    protected $daysLeft;

    public function __construct($daysLeft)
    {
        $this->daysLeft = $daysLeft;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Subscription is Expiring Soon')
            ->line("Your subscription will expire in {$this->daysLeft} days.")
            ->line("Current points: {$notifiable->points}")
            ->line('Renew your subscription to keep your points and continue downloading.')
            ->action('Renew Now', route('profile.edit'));
    }

    public function toArray($notifiable)
    {
        return [
            'message' => "Your subscription will expire in {$this->daysLeft} days.",
            'days_left' => $this->daysLeft,
            'current_points' => $notifiable->points,
            'type' => 'subscription_expiring'
        ];
    }
}
