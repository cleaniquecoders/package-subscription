<?php

namespace CleaniqueCoders\PackageSubscription\Events;

use CleaniqueCoders\PackageSubscription\Models\Usage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UsageLimitExceeded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Usage $usage
    ) {}
}
