<?php

namespace CleaniqueCoders\PackageSubscription\Notifications;

use CleaniqueCoders\PackageSubscription\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Subscription $subscription,
        public bool $immediately = false
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Subscription Cancelled')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your subscription to '.$this->subscription->plan->name.' has been cancelled.');

        if ($this->immediately) {
            $message->line('Your subscription has been cancelled immediately.');
        } else {
            $message->line('You will continue to have access until '.$this->subscription->ends_at->format('F j, Y').'.');
        }

        return $message
            ->line('We hope to see you again soon!')
            ->action('View Plans', url('/plans'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name,
            'cancelled_immediately' => $this->immediately,
            'ends_at' => $this->subscription->ends_at->toDateTimeString(),
        ];
    }
}
