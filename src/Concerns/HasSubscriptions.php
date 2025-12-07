<?php

namespace CleaniqueCoders\PackageSubscription\Concerns;

use CleaniqueCoders\PackageSubscription\Models\Plan;
use CleaniqueCoders\PackageSubscription\Models\Subscription;
use CleaniqueCoders\PackageSubscription\Models\Usage;
use CleaniqueCoders\PackageSubscription\Services\SubscriptionService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/** @phpstan-ignore-next-line */
trait HasSubscriptions
{
    /**
     * Get all subscriptions for this model
     */
    public function subscriptions(): MorphMany
    {
        return $this->morphMany(config('package-subscription.models.subscription', Subscription::class), 'subscribable');
    }

    /**
     * Subscribe to a plan
     */
    public function subscribeTo(Plan|string $plan, array $options = []): Subscription
    {
        if (is_string($plan)) {
            $plan = Plan::where('slug', $plan)->firstOrFail();
        }

        return app(SubscriptionService::class)->create($this, $plan, $options);
    }

    /**
     * Get the active subscription
     */
    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->whereIn('status', ['active', 'on_trial'])
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->latest()
            ->first();
    }

    /**
     * Get the active subscription (alias)
     */
    public function subscription(): ?Subscription
    {
        return $this->activeSubscription();
    }

    /**
     * Check if the model has an active subscription
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /**
     * Check if has subscription (alias)
     */
    public function hasSubscription(): bool
    {
        return $this->hasActiveSubscription();
    }

    /**
     * Check if subscribed (alias)
     */
    public function isSubscribed(): bool
    {
        return $this->hasActiveSubscription();
    }

    /**
     * Check if subscribed to a specific plan
     */
    public function subscribedTo(Plan|string $plan): bool
    {
        $subscription = $this->activeSubscription();

        if (! $subscription) {
            return false;
        }

        if ($plan instanceof Plan) {
            return $subscription->plan_id === $plan->id;
        }

        return $subscription->plan->slug === $plan;
    }

    /**
     * Check if subscribed to plan (alias)
     */
    public function isSubscribedTo(Plan|string $plan): bool
    {
        return $this->subscribedTo($plan);
    }

    /**
     * Check if subscribed to a plan by slug
     */
    public function subscribedToPlan(string $planSlug): bool
    {
        return $this->subscribedTo($planSlug);
    }

    /**
     * Check if on trial
     */
    public function onTrial(): bool
    {
        $subscription = $this->activeSubscription();

        return $subscription && $subscription->isOnTrial();
    }

    /**
     * Check if on grace period
     */
    public function onGracePeriod(): bool
    {
        $subscription = $this->activeSubscription();

        return $subscription && $subscription->isOnGracePeriod();
    }

    /**
     * Get subscription history
     */
    public function subscriptionHistory(): Collection
    {
        return $this->subscriptions()
            ->with(['plan', 'history'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Check if can use a specific feature
     */
    public function canUseFeature(string $feature): bool
    {
        $subscription = $this->activeSubscription();

        return $subscription && $subscription->canUseFeature($feature);
    }

    /**
     * Check if has feature (alias)
     */
    public function hasFeature(string $feature): bool
    {
        return $this->canUseFeature($feature);
    }

    /**
     * Get feature value
     */
    public function getFeatureValue(string $feature): mixed
    {
        $subscription = $this->activeSubscription();

        return $subscription ? $subscription->getFeatureValue($feature) : null;
    }

    /**
     * Get feature limit
     */
    public function getFeatureLimit(string $feature): ?int
    {
        $subscription = $this->activeSubscription();

        return $subscription ? $subscription->getFeatureLimit($feature) : null;
    }

    /**
     * Record usage for a feature
     */
    public function recordUsage(string $feature, float $amount): ?Usage
    {
        $subscription = $this->activeSubscription();

        return $subscription ? $subscription->recordUsage($feature, $amount) : null;
    }

    /**
     * Increment usage for a feature
     */
    public function incrementUsage(string $feature, float $amount = 1): ?Usage
    {
        $subscription = $this->activeSubscription();

        return $subscription ? $subscription->incrementUsage($feature, $amount) : null;
    }

    /**
     * Decrement usage for a feature
     */
    public function decrementUsage(string $feature, float $amount = 1): ?Usage
    {
        $subscription = $this->activeSubscription();

        return $subscription ? $subscription->decrementUsage($feature, $amount) : null;
    }

    /**
     * Set usage for a feature
     */
    public function setUsage(string $feature, float $amount): ?Usage
    {
        $subscription = $this->activeSubscription();

        return $subscription ? $subscription->setUsage($feature, $amount) : null;
    }

    /**
     * Get current usage for a feature
     */
    public function getUsage(string $feature): float
    {
        $subscription = $this->activeSubscription();

        return $subscription ? $subscription->getUsage($feature) : 0;
    }

    /**
     * Get remaining usage for a feature
     */
    public function getRemainingUsage(string $feature): ?float
    {
        $subscription = $this->activeSubscription();

        return $subscription ? $subscription->getRemainingUsage($feature) : null;
    }

    /**
     * Get usage percentage for a feature
     */
    public function getUsagePercentage(string $feature): float
    {
        $subscription = $this->activeSubscription();

        return $subscription ? $subscription->getUsagePercentage($feature) : 0;
    }

    /**
     * Check if usage exceeds limit
     */
    public function exceedsLimit(string $feature): bool
    {
        $subscription = $this->activeSubscription();

        return $subscription && $subscription->exceedsLimit($feature);
    }

    /**
     * Check if within limit
     */
    public function withinLimit(string $feature, float $proposed = 0): bool
    {
        $subscription = $this->activeSubscription();

        return $subscription ? $subscription->withinLimit($feature, $proposed) : false;
    }

    /**
     * Reset usage
     */
    public function resetUsage(?string $feature = null): void
    {
        $subscription = $this->activeSubscription();

        if ($subscription) {
            $subscription->resetUsage($feature);
        }
    }

    /**
     * Upgrade to a plan
     */
    public function upgradeTo(Plan|string $plan, array $options = []): Subscription
    {
        if (is_string($plan)) {
            $plan = Plan::where('slug', $plan)->firstOrFail();
        }

        $subscription = $this->activeSubscription();

        if (! $subscription) {
            throw new \RuntimeException('No active subscription to upgrade');
        }

        return $subscription->upgradeTo($plan, $options);
    }

    /**
     * Downgrade to a plan
     */
    public function downgradeTo(Plan|string $plan, array $options = []): Subscription
    {
        if (is_string($plan)) {
            $plan = Plan::where('slug', $plan)->firstOrFail();
        }

        $subscription = $this->activeSubscription();

        if (! $subscription) {
            throw new \RuntimeException('No active subscription to downgrade');
        }

        return $subscription->downgradeTo($plan, $options);
    }

    /**
     * Switch plan
     */
    public function switchPlan(Plan|string $plan, array $options = []): Subscription
    {
        if (is_string($plan)) {
            $plan = Plan::where('slug', $plan)->firstOrFail();
        }

        $subscription = $this->activeSubscription();

        if (! $subscription) {
            throw new \RuntimeException('No active subscription to switch');
        }

        return $subscription->switchTo($plan, $options);
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(bool $immediately = false): ?Subscription
    {
        $subscription = $this->activeSubscription();

        return $subscription ? $subscription->cancel($immediately) : null;
    }

    /**
     * Resume subscription
     */
    public function resumeSubscription(): ?Subscription
    {
        $subscription = $this->subscriptions()
            ->where('status', 'cancelled')
            ->latest()
            ->first();

        return $subscription ? $subscription->resume() : null;
    }
}
