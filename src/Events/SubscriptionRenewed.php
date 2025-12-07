<?php

namespace CleaniqueCoders\PackageSubscription\Events;

use Carbon\Carbon;
use CleaniqueCoders\PackageSubscription\Models\Subscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionRenewed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Subscription $subscription,
        public Carbon $previousEndDate,
        public Carbon $newEndDate
    ) {}
}
