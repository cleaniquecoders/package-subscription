<?php

namespace CleaniqueCoders\PackageSubscription\Services;

use CleaniqueCoders\PackageSubscription\Events\UsageLimitExceeded;
use CleaniqueCoders\PackageSubscription\Events\UsageRecorded;
use CleaniqueCoders\PackageSubscription\Models\Subscription;
use CleaniqueCoders\PackageSubscription\Models\Usage;

class UsageService
{
    /**
     * Record usage for a subscription feature
     */
    public function record(Subscription $subscription, string $feature, float $amount): Usage
    {
        $usage = $this->getOrCreateUsage($subscription, $feature);

        $usage->update([
            'used' => $amount,
        ]);

        event(new UsageRecorded($usage, $amount));

        if ($usage->isExceeded()) {
            event(new UsageLimitExceeded($usage));
        }

        return $usage->fresh();
    }

    /**
     * Increment usage for a subscription feature
     */
    public function increment(Subscription $subscription, string $feature, float $amount = 1): Usage
    {
        $usage = $this->getOrCreateUsage($subscription, $feature);

        $usage->incrementUsage($amount);

        event(new UsageRecorded($usage, $amount));

        if ($usage->isExceeded()) {
            event(new UsageLimitExceeded($usage));
        }

        return $usage->fresh();
    }

    /**
     * Decrement usage for a subscription feature
     */
    public function decrement(Subscription $subscription, string $feature, float $amount = 1): Usage
    {
        $usage = $this->getOrCreateUsage($subscription, $feature);

        $usage->decrementUsage($amount);

        return $usage->fresh();
    }

    /**
     * Set usage for a subscription feature
     */
    public function set(Subscription $subscription, string $feature, float $amount): Usage
    {
        return $this->record($subscription, $feature, $amount);
    }

    /**
     * Reset usage for a subscription (all features or specific feature)
     */
    public function reset(Subscription $subscription, ?string $feature = null): void
    {
        $query = $subscription->usages();

        if ($feature) {
            $query->where('feature', $feature);
        }

        $query->update([
            'used' => 0,
            'reset_at' => now(),
        ]);
    }

    /**
     * Get current usage for a feature
     */
    public function get(Subscription $subscription, string $feature): float
    {
        $usage = $this->getOrCreateUsage($subscription, $feature);

        return (float) $usage->used;
    }

    /**
     * Get remaining usage for a feature
     */
    public function getRemaining(Subscription $subscription, string $feature): ?float
    {
        $usage = $this->getOrCreateUsage($subscription, $feature);

        return $usage->getRemainingAmount();
    }

    /**
     * Check if proposed usage is within limit
     */
    public function checkLimit(Subscription $subscription, string $feature, float $proposed = 0): bool
    {
        $usage = $this->getOrCreateUsage($subscription, $feature);

        if ($usage->limit === null) {
            return true; // No limit
        }

        return ($usage->used + $proposed) <= $usage->limit;
    }

    /**
     * Get or create usage record for a feature
     */
    protected function getOrCreateUsage(Subscription $subscription, string $feature): Usage
    {
        /** @var Usage $usage */
        $usage = $subscription->usages()->firstOrCreate(
            ['feature' => $feature],
            [
                'used' => 0,
                'limit' => $subscription->getFeatureLimit($feature),
                'valid_until' => $subscription->ends_at,
            ]
        );

        return $usage;
    }
}
