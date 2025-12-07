<?php

namespace CleaniqueCoders\PackageSubscription\Commands;

use CleaniqueCoders\PackageSubscription\Models\Subscription;
use CleaniqueCoders\PackageSubscription\Notifications\SubscriptionExpiredNotification;
use Illuminate\Console\Command;

class CheckExpiredSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:check-expired';

    protected $description = 'Check and expire subscriptions that have ended';

    public function handle(): int
    {
        $this->info('Checking for expired subscriptions...');

        $expiredSubscriptions = Subscription::whereIn('status', ['active', 'on_trial', 'cancelled'])
            ->where('ends_at', '<', now())
            ->get();

        $count = $expiredSubscriptions->count();

        if ($count === 0) {
            $this->info('No expired subscriptions found.');

            return self::SUCCESS;
        }

        $this->info("Found {$count} expired subscription(s).");

        foreach ($expiredSubscriptions as $subscription) {
            $subscription->expire();

            // Notify the subscriber
            /** @phpstan-ignore-next-line */
            if ($subscription->subscribable && method_exists($subscription->subscribable, 'notify')) {
                $subscription->subscribable->notify(new SubscriptionExpiredNotification($subscription));
            }

            $this->line("Expired subscription #{$subscription->id} for {$subscription->plan->name}");
        }

        $this->info("Successfully expired {$count} subscription(s).");

        return self::SUCCESS;
    }
}
