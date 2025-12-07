<?php

namespace CleaniqueCoders\PackageSubscription\Database\Factories;

use CleaniqueCoders\PackageSubscription\Enums\BillingPeriod;
use CleaniqueCoders\PackageSubscription\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug(2),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 0, 999),
            'billing_period' => $this->faker->randomElement([
                BillingPeriod::MONTHLY,
                BillingPeriod::YEARLY,
            ]),
            'billing_interval' => 1,
            'trial_period_days' => 0,
            'grace_period_days' => 3,
            'features' => [
                'api_calls' => $this->faker->numberBetween(100, 10000),
                'storage' => $this->faker->numberBetween(1, 100),
                'projects' => $this->faker->numberBetween(1, 50),
                'users' => $this->faker->numberBetween(1, 20),
            ],
            'metadata' => [],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    /**
     * Indicate that the plan is free
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => 0,
            'features' => [
                'api_calls' => 100,
                'storage' => 1,
                'projects' => 1,
                'users' => 1,
            ],
        ]);
    }

    /**
     * Indicate that the plan has a trial period
     */
    public function withTrial(int $days = 14): static
    {
        return $this->state(fn (array $attributes) => [
            'trial_period_days' => $days,
        ]);
    }

    /**
     * Indicate that the plan is inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the plan is monthly
     */
    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_period' => BillingPeriod::MONTHLY,
        ]);
    }

    /**
     * Indicate that the plan is yearly
     */
    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_period' => BillingPeriod::YEARLY,
        ]);
    }

    /**
     * Indicate that the plan is lifetime
     */
    public function lifetime(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_period' => BillingPeriod::LIFETIME,
        ]);
    }
}
