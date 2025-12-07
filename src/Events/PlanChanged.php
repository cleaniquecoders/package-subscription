<?php

namespace CleaniqueCoders\PackageSubscription\Events;

use CleaniqueCoders\PackageSubscription\Models\Plan;
use CleaniqueCoders\PackageSubscription\Models\Subscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlanChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Subscription $subscription,
        public Plan $fromPlan,
        public Plan $toPlan,
        public string $changeType, // upgrade, downgrade, switch
        public float $prorationAmount
    ) {}
}
