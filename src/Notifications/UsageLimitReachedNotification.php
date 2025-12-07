<?php

namespace CleaniqueCoders\PackageSubscription\Notifications;

use CleaniqueCoders\PackageSubscription\Models\Usage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UsageLimitReachedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Usage $usage
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Usage Limit Reached')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('You have reached your usage limit for '.$this->usage->feature.'.')
            ->line('Current usage: '.$this->usage->used.' / '.$this->usage->limit)
            ->action('Upgrade Plan', url('/plans'))
            ->line('Upgrade to continue using this feature.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'usage_id' => $this->usage->id,
            'feature' => $this->usage->feature,
            'used' => $this->usage->used,
            'limit' => $this->usage->limit,
        ];
    }
}
