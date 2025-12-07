<?php

namespace CleaniqueCoders\PackageSubscription\Database\Factories;

use CleaniqueCoders\PackageSubscription\Models\Subscription;
use CleaniqueCoders\PackageSubscription\Models\Usage;
use Illuminate\Database\Eloquent\Factories\Factory;

class UsageFactory extends Factory
{
    protected $model = Usage::class;

    public function definition(): array
    {
        return [
            'subscription_id' => Subscription::factory(),
            'feature' => $this->faker->randomElement(['api_calls', 'storage', 'projects', 'users']),
            'used' => $this->faker->numberBetween(0, 100),
            'limit' => $this->faker->numberBetween(100, 1000),
            'valid_until' => now()->endOfMonth(),
            'reset_at' => now()->startOfMonth(),
        ];
    }

    /**
     * Indicate that the usage has no limit
     */
    public function unlimited(): static
    {
        return $this->state(fn (array $attributes) => [
            'limit' => null,
        ]);
    }

    /**
     * Indicate that the usage has exceeded the limit
     */
    public function exceeded(): static
    {
        return $this->state(fn (array $attributes) => [
            'used' => ($attributes['limit'] ?? 100) + 10,
        ]);
    }
}
