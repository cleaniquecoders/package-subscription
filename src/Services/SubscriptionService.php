<?php

namespace CleaniqueCoders\PackageSubscription\Services;

use CleaniqueCoders\PackageSubscription\Enums\SubscriptionStatus;
use CleaniqueCoders\PackageSubscription\Events\SubscriptionCreated;
use CleaniqueCoders\PackageSubscription\Models\Plan;
use CleaniqueCoders\PackageSubscription\Models\Subscription;
use Illuminate\Database\Eloquent\Model;

class SubscriptionService
{
    /**
     * Create a new subscription
     */
    public function create(Model $subscribable, Plan $plan, array $options = []): Subscription
    {
        $now = now();
        $trialEndsAt = null;
        $startsAt = $options['starts_at'] ?? $now;

        // Determine if on trial
        $isOnTrial = false;
        if ($plan->hasTrial() && ($options['with_trial'] ?? true)) {
            $isOnTrial = true;
            $trialEndsAt = $now->copy()->addDays($options['trial_days'] ?? $plan->trial_period_days);
        }

        // Calculate end date
        $endsAt = $plan->calculateNextBillingDate($startsAt);

        /** @phpstan-ignore-next-line */
        $subscription = $subscribable->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => $isOnTrial ? SubscriptionStatus::ON_TRIAL : SubscriptionStatus::ACTIVE,
            'trial_ends_at' => $trialEndsAt,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'price' => $plan->price,
            'billing_period' => $plan->billing_period->value,
            'snapshot' => $plan->features,
            'metadata' => $options['metadata'] ?? [],
        ]);

        // Record in history
        $subscription->history()->create([
            'event_type' => 'created',
            'to_plan_id' => $plan->id,
            'metadata' => $options['metadata'] ?? [],
        ]);

        event(new SubscriptionCreated($subscription, $options['metadata'] ?? []));

        return $subscription->fresh();
    }

    /**
     * Renew a subscription
     */
    public function renew(Subscription $subscription): Subscription
    {
        return $subscription->renew();
    }

    /**
     * Cancel a subscription
     */
    public function cancel(Subscription $subscription, bool $immediately = false): Subscription
    {
        return $subscription->cancel($immediately);
    }

    /**
     * Suspend a subscription
     */
    public function suspend(Subscription $subscription, ?string $reason = null): Subscription
    {
        return $subscription->suspend($reason);
    }

    /**
     * Resume a subscription
     */
    public function resume(Subscription $subscription): Subscription
    {
        return $subscription->resume();
    }

    /**
     * Expire a subscription
     */
    public function expire(Subscription $subscription): Subscription
    {
        return $subscription->expire();
    }

    /**
     * Change plan for a subscription
     */
    public function changePlan(Subscription $subscription, Plan $newPlan, array $options = []): Subscription
    {
        return $subscription->switchTo($newPlan, $options);
    }
}
