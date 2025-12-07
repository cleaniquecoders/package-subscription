<?php

namespace CleaniqueCoders\PackageSubscription\Notifications;

use CleaniqueCoders\PackageSubscription\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiredNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Subscription $subscription
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Subscription Has Expired')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your subscription to '.$this->subscription->plan->name.' has expired.')
            ->line('You no longer have access to premium features.')
            ->action('Renew Now', url('/subscriptions/renew'))
            ->line('We would love to have you back!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name,
            'expired_at' => $this->subscription->ends_at->toDateTimeString(),
        ];
    }
}
