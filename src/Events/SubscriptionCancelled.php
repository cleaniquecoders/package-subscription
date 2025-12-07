<?php

namespace CleaniqueCoders\PackageSubscription\Events;

use CleaniqueCoders\PackageSubscription\Models\Subscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionCancelled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Subscription $subscription,
        public bool $immediately,
        public ?string $reason = null
    ) {}
}
