<?php

namespace CleaniqueCoders\PackageSubscription\Notifications;

use CleaniqueCoders\PackageSubscription\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Subscription $subscription,
        public int $daysRemaining
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Subscription is Expiring Soon')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your subscription to '.$this->subscription->plan->name.' will expire in '.$this->daysRemaining.' days.')
            ->line('Expiration date: '.$this->subscription->ends_at->format('F j, Y'))
            ->action('Renew Subscription', url('/subscriptions/renew'))
            ->line('Don\'t lose access to your features!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name,
            'days_remaining' => $this->daysRemaining,
            'expires_at' => $this->subscription->ends_at->toDateTimeString(),
        ];
    }
}
