<?php

namespace CleaniqueCoders\PackageSubscription\Notifications;

use CleaniqueCoders\PackageSubscription\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionRenewedNotification extends Notification
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
            ->subject('Subscription Renewed')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your subscription to '.$this->subscription->plan->name.' has been successfully renewed.')
            ->line('Your next billing date is '.$this->subscription->ends_at->format('F j, Y').'.')
            ->action('View Subscription', url('/subscriptions'))
            ->line('Thank you for your continued support!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name,
            'next_billing_date' => $this->subscription->ends_at->toDateTimeString(),
        ];
    }
}
