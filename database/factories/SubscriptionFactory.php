<?php

namespace CleaniqueCoders\PackageSubscription\Database\Factories;

use CleaniqueCoders\PackageSubscription\Enums\BillingPeriod;
use CleaniqueCoders\PackageSubscription\Enums\SubscriptionStatus;
use CleaniqueCoders\PackageSubscription\Models\Plan;
use CleaniqueCoders\PackageSubscription\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        $starts_at = now();
        $ends_at = $starts_at->copy()->addMonth();

        return [
            'plan_id' => Plan::factory(),
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => $starts_at,
            'ends_at' => $ends_at,
            'price' => $this->faker->randomFloat(2, 0, 999),
            'billing_period' => BillingPeriod::MONTHLY->value,
            'snapshot' => null,
            'metadata' => [],
        ];
    }

    /**
     * Indicate that the subscription is on trial
     */
    public function onTrial(int $days = 14): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::ON_TRIAL,
            'trial_ends_at' => now()->addDays($days),
        ]);
    }

    /**
     * Indicate that the subscription is cancelled
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::CANCELLED,
            'cancelled_at' => now()->subDays(5),
        ]);
    }

    /**
     * Indicate that the subscription is expired
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::EXPIRED,
            'ends_at' => now()->subDays(10),
        ]);
    }

    /**
     * Indicate that the subscription is suspended
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriptionStatus::SUSPENDED,
            'suspended_at' => now()->subDays(3),
        ]);
    }

    /**
     * Indicate that the subscription has a grace period
     */
    public function withGracePeriod(int $days = 3): static
    {
        return $this->state(fn (array $attributes) => [
            'grace_ends_at' => now()->addDays($days),
        ]);
    }
}
