<?php

namespace CleaniqueCoders\PackageSubscription\Services;

use Carbon\Carbon;
use CleaniqueCoders\PackageSubscription\Models\Plan;
use CleaniqueCoders\PackageSubscription\Models\Subscription;

class ProrationService
{
    /**
     * Calculate proration amount for plan change
     */
    public function calculate(Subscription $subscription, Plan $newPlan, ?Carbon $effectiveDate = null): float
    {
        if (! config('package-subscription.proration.enabled', true)) {
            return 0;
        }

        $effectiveDate = $effectiveDate ?? now();

        // No proration for lifetime plans
        if ($subscription->plan->isLifetime() || $newPlan->isLifetime()) {
            return 0;
        }

        // Calculate credit from remaining time on current plan
        $credit = $this->calculateCredit($subscription, $effectiveDate);

        // Calculate charge for remaining time on new plan
        $charge = $this->calculateCharge($newPlan, $effectiveDate, $subscription->ends_at);

        $proration = $charge - $credit;

        // Round based on configuration
        $decimals = config('package-subscription.proration.rounding', 2);

        return round($proration, $decimals);
    }

    /**
     * Calculate credit from unused time on current subscription
     */
    public function calculateCredit(Subscription $subscription, Carbon $date): float
    {
        /** @phpstan-ignore-next-line */
        if (! $subscription->ends_at || $date->isAfter($subscription->ends_at)) {
            return 0;
        }

        $totalDays = $subscription->starts_at->diffInDays($subscription->ends_at);
        $remainingDays = $date->diffInDays($subscription->ends_at);

        if ($totalDays <= 0) {
            return 0;
        }

        $dailyRate = $subscription->price / $totalDays;

        return $dailyRate * $remainingDays;
    }

    /**
     * Calculate charge for time on new plan
     */
    public function calculateCharge(Plan $plan, Carbon $from, Carbon $to): float
    {
        $totalDays = $from->diffInDays($to);

        if ($totalDays <= 0) {
            return 0;
        }

        // Calculate based on billing period
        $periodDays = $plan->billing_period->days();

        if ($periodDays <= 0) {
            return $plan->price; // Lifetime
        }

        $dailyRate = $plan->price / $periodDays;

        return $dailyRate * $totalDays;
    }

    /**
     * Determine if proration should be applied
     */
    public function shouldProrate(Plan $fromPlan, Plan $toPlan): bool
    {
        if (! config('package-subscription.proration.enabled', true)) {
            return false;
        }

        // No proration between lifetime plans
        if ($fromPlan->isLifetime() && $toPlan->isLifetime()) {
            return false;
        }

        // No proration for free plans
        if ($fromPlan->isFree() && $toPlan->isFree()) {
            return false;
        }

        return true;
    }

    /**
     * Calculate prorated refund for cancellation
     */
    public function calculateRefund(Subscription $subscription, ?Carbon $cancelDate = null): float
    {
        $cancelDate = $cancelDate ?? now();

        return max(0, $this->calculateCredit($subscription, $cancelDate));
    }
}
