<?php

namespace CleaniqueCoders\PackageSubscription\Commands;

use CleaniqueCoders\PackageSubscription\Models\Subscription;
use CleaniqueCoders\PackageSubscription\Notifications\SubscriptionRenewedNotification;
use Illuminate\Console\Command;

class RenewSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:renew {--dry-run : Show subscriptions that would be renewed without renewing them}';

    protected $description = 'Process subscription renewals';

    public function handle(): int
    {
        $this->info('Processing subscription renewals...');

        $dryRun = $this->option('dry-run');

        // Find subscriptions that need renewal (ending within next day)
        $subscriptions = Subscription::where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now()->addDay())
            ->where('ends_at', '>', now())
            ->whereNull('cancelled_at')
            ->get();

        $count = $subscriptions->count();

        if ($count === 0) {
            $this->info('No subscriptions due for renewal.');

            return self::SUCCESS;
        }

        $this->info("Found {$count} subscription(s) due for renewal.");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No subscriptions will be renewed');

            foreach ($subscriptions as $subscription) {
                $this->line("Would renew subscription #{$subscription->id} - {$subscription->plan->name}");
            }

            return self::SUCCESS;
        }

        foreach ($subscriptions as $subscription) {
            try {
                $subscription->renew();

                // Notify the subscriber
                /** @phpstan-ignore-next-line */
                if ($subscription->subscribable && method_exists($subscription->subscribable, 'notify')) {
                    $subscription->subscribable->notify(new SubscriptionRenewedNotification($subscription));
                }

                $this->line("Renewed subscription #{$subscription->id} for {$subscription->plan->name}");
            } catch (\Exception $e) {
                $this->error("Failed to renew subscription #{$subscription->id}: {$e->getMessage()}");
            }
        }

        $this->info("Successfully processed {$count} subscription renewal(s).");

        return self::SUCCESS;
    }
}
