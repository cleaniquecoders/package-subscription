<?php

namespace CleaniqueCoders\PackageSubscription\Commands;

use CleaniqueCoders\PackageSubscription\Models\Subscription;
use Illuminate\Console\Command;

class ResetUsageCommand extends Command
{
    protected $signature = 'subscriptions:reset-usage {--subscription= : The ID of the subscription to reset usage for}';

    protected $description = 'Reset usage counters for subscriptions';

    public function handle(): int
    {
        $subscriptionId = $this->option('subscription');

        if ($subscriptionId) {
            return $this->resetSingleSubscription((int) $subscriptionId);
        }

        return $this->resetAllSubscriptions();
    }

    protected function resetSingleSubscription(int $subscriptionId): int
    {
        $subscription = Subscription::find($subscriptionId);

        if (! $subscription) {
            $this->error("Subscription #{$subscriptionId} not found.");

            return self::FAILURE;
        }

        $subscription->resetUsage();

        $this->info("Usage reset for subscription #{$subscriptionId}.");

        return self::SUCCESS;
    }

    protected function resetAllSubscriptions(): int
    {
        $this->info('Resetting usage for all active subscriptions...');

        $subscriptions = Subscription::active()->get();

        $count = $subscriptions->count();

        if ($count === 0) {
            $this->info('No active subscriptions found.');

            return self::SUCCESS;
        }

        foreach ($subscriptions as $subscription) {
            $subscription->resetUsage();
            $this->line("Reset usage for subscription #{$subscription->id}");
        }

        $this->info("Successfully reset usage for {$count} subscription(s).");

        return self::SUCCESS;
    }
}
