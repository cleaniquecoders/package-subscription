<?php

namespace CleaniqueCoders\PackageSubscription\Notifications;

use CleaniqueCoders\PackageSubscription\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionCreatedNotification extends Notification
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
            ->subject('Welcome to '.$this->subscription->plan->name)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Thank you for subscribing to '.$this->subscription->plan->name.'.')
            ->line('Your subscription is now active.')
            ->when($this->subscription->isOnTrial(), function ($mail) {
                $mail->line('You are currently on a trial period until '.$this->subscription->trial_ends_at->format('F j, Y').'.');
            })
            ->action('View Subscription', url('/subscriptions'))
            ->line('Thank you for choosing us!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan->name,
            'status' => $this->subscription->status->value,
        ];
    }
}
