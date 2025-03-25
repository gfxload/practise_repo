<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpired extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Subscription Has Expired')
            ->line('Your subscription has expired.')
            ->line("You have {$this->data['points_on_hold']} points on hold.")
            ->line('Renew your subscription to keep your points and continue downloading.')
            ->action('Renew Now', route('profile.edit'));
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Your subscription has expired. You have ' . $this->data['points_on_hold'] . ' points on hold.',
            'points_on_hold' => $this->data['points_on_hold'],
            'type' => 'subscription_expired'
        ];
    }
}
