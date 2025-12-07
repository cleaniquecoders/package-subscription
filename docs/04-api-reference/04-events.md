# Events

The package dispatches events at key subscription lifecycle points.

## Event Configuration

Configure event behavior in `config/package-subscription.php`:

```php
'events' => [
    'dispatch' => true,   // Enable/disable events
    'queue' => false,     // Queue event handlers
],
```

## Subscription Events

### SubscriptionCreated

`CleaniqueCoders\PackageSubscription\Events\SubscriptionCreated`

Fired when a new subscription is created.

**Properties:**

| Property | Type | Description |
|----------|------|-------------|
| `subscription` | Subscription | The new subscription |
| `metadata` | array | Creation metadata |

**Example Listener:**

```php
use CleaniqueCoders\PackageSubscription\Events\SubscriptionCreated;

class SendWelcomeEmail
{
    public function handle(SubscriptionCreated $event): void
    {
        $subscription = $event->subscription;
        $user = $subscription->subscribable;

        $user->notify(new WelcomeToSubscriptionNotification(
            plan: $subscription->plan,
            trialEndsAt: $subscription->trial_ends_at,
        ));
    }
}
```

---

### SubscriptionRenewed

`CleaniqueCoders\PackageSubscription\Events\SubscriptionRenewed`

Fired when a subscription is renewed.

**Properties:**

| Property | Type | Description |
|----------|------|-------------|
| `subscription` | Subscription | The renewed subscription |
| `previousEndsAt` | Carbon | Previous end date |

**Example Listener:**

```php
use CleaniqueCoders\PackageSubscription\Events\SubscriptionRenewed;

class SendRenewalReceipt
{
    public function handle(SubscriptionRenewed $event): void
    {
        $subscription = $event->subscription;
        $user = $subscription->subscribable;

        $user->notify(new RenewalReceiptNotification(
            plan: $subscription->plan,
            amount: $subscription->price,
            nextRenewal: $subscription->ends_at,
        ));
    }
}
```

---

### SubscriptionCancelled

`CleaniqueCoders\PackageSubscription\Events\SubscriptionCancelled`

Fired when a subscription is cancelled.

**Properties:**

| Property | Type | Description |
|----------|------|-------------|
| `subscription` | Subscription | The cancelled subscription |
| `immediately` | bool | Whether cancelled immediately |

**Example Listener:**

```php
use CleaniqueCoders\PackageSubscription\Events\SubscriptionCancelled;

class HandleCancellation
{
    public function handle(SubscriptionCancelled $event): void
    {
        $subscription = $event->subscription;
        $user = $subscription->subscribable;

        if ($event->immediately) {
            // Immediate cancellation
            $user->notify(new ImmediateCancellationNotification());
        } else {
            // End of period cancellation
            $user->notify(new CancellationScheduledNotification(
                endsAt: $subscription->ends_at,
            ));
        }

        // Log cancellation
        activity()
            ->causedBy($user)
            ->performedOn($subscription)
            ->log('Subscription cancelled');
    }
}
```

---

### SubscriptionSuspended

`CleaniqueCoders\PackageSubscription\Events\SubscriptionSuspended`

Fired when a subscription is suspended.

**Properties:**

| Property | Type | Description |
|----------|------|-------------|
| `subscription` | Subscription | The suspended subscription |
| `reason` | string\|null | Suspension reason |

**Example Listener:**

```php
use CleaniqueCoders\PackageSubscription\Events\SubscriptionSuspended;

class HandleSuspension
{
    public function handle(SubscriptionSuspended $event): void
    {
        $subscription = $event->subscription;
        $user = $subscription->subscribable;

        $user->notify(new SubscriptionSuspendedNotification(
            reason: $event->reason,
        ));

        // Notify admin
        Notification::route('slack', config('services.slack.webhook'))
            ->notify(new AdminSubscriptionSuspendedNotification($subscription));
    }
}
```

---

### SubscriptionResumed

`CleaniqueCoders\PackageSubscription\Events\SubscriptionResumed`

Fired when a subscription is resumed.

**Properties:**

| Property | Type | Description |
|----------|------|-------------|
| `subscription` | Subscription | The resumed subscription |

**Example Listener:**

```php
use CleaniqueCoders\PackageSubscription\Events\SubscriptionResumed;

class HandleResumption
{
    public function handle(SubscriptionResumed $event): void
    {
        $subscription = $event->subscription;
        $user = $subscription->subscribable;

        $user->notify(new SubscriptionResumedNotification(
            plan: $subscription->plan,
        ));
    }
}
```

---

### SubscriptionExpired

`CleaniqueCoders\PackageSubscription\Events\SubscriptionExpired`

Fired when a subscription expires.

**Properties:**

| Property | Type | Description |
|----------|------|-------------|
| `subscription` | Subscription | The expired subscription |

**Example Listener:**

```php
use CleaniqueCoders\PackageSubscription\Events\SubscriptionExpired;

class HandleExpiration
{
    public function handle(SubscriptionExpired $event): void
    {
        $subscription = $event->subscription;
        $user = $subscription->subscribable;

        $user->notify(new SubscriptionExpiredNotification());

        // Downgrade to free features
        $this->downgradeToFree($user);
    }

    private function downgradeToFree(User $user): void
    {
        // Implement downgrade logic
    }
}
```

---

### PlanChanged

`CleaniqueCoders\PackageSubscription\Events\PlanChanged`

Fired when a subscription's plan is changed.

**Properties:**

| Property | Type | Description |
|----------|------|-------------|
| `subscription` | Subscription | The subscription |
| `oldPlan` | Plan | Previous plan |
| `newPlan` | Plan | New plan |
| `proration` | array\|null | Proration details |

**Example Listener:**

```php
use CleaniqueCoders\PackageSubscription\Events\PlanChanged;

class HandlePlanChange
{
    public function handle(PlanChanged $event): void
    {
        $subscription = $event->subscription;
        $user = $subscription->subscribable;
        $isUpgrade = $event->newPlan->price > $event->oldPlan->price;

        if ($isUpgrade) {
            $user->notify(new PlanUpgradedNotification(
                oldPlan: $event->oldPlan,
                newPlan: $event->newPlan,
            ));
        } else {
            $user->notify(new PlanDowngradedNotification(
                oldPlan: $event->oldPlan,
                newPlan: $event->newPlan,
            ));
        }
    }
}
```

---

## Usage Events

### UsageRecorded

`CleaniqueCoders\PackageSubscription\Events\UsageRecorded`

Fired when usage is recorded.

**Properties:**

| Property | Type | Description |
|----------|------|-------------|
| `usage` | Usage | The usage record |
| `amount` | float | Amount recorded |

**Example Listener:**

```php
use CleaniqueCoders\PackageSubscription\Events\UsageRecorded;

class LogUsage
{
    public function handle(UsageRecorded $event): void
    {
        Log::channel('usage')->info('Usage recorded', [
            'subscription_id' => $event->usage->subscription_id,
            'feature' => $event->usage->feature,
            'amount' => $event->amount,
            'total' => $event->usage->used,
        ]);
    }
}
```

---

### UsageLimitExceeded

`CleaniqueCoders\PackageSubscription\Events\UsageLimitExceeded`

Fired when usage exceeds the feature limit.

**Properties:**

| Property | Type | Description |
|----------|------|-------------|
| `usage` | Usage | The usage record |

**Example Listener:**

```php
use CleaniqueCoders\PackageSubscription\Events\UsageLimitExceeded;

class HandleLimitExceeded
{
    public function handle(UsageLimitExceeded $event): void
    {
        $usage = $event->usage;
        $subscription = $usage->subscription;
        $user = $subscription->subscribable;

        $user->notify(new UsageLimitExceededNotification(
            feature: $usage->feature,
            used: $usage->used,
            limit: $usage->limit,
        ));

        // Notify account manager for enterprise accounts
        if ($subscription->plan->slug === 'enterprise') {
            $this->notifyAccountManager($subscription);
        }
    }
}
```

---

## Registering Listeners

### EventServiceProvider

```php
<?php

namespace App\Providers;

use CleaniqueCoders\PackageSubscription\Events\SubscriptionCreated;
use CleaniqueCoders\PackageSubscription\Events\SubscriptionCancelled;
use CleaniqueCoders\PackageSubscription\Events\SubscriptionRenewed;
use CleaniqueCoders\PackageSubscription\Events\SubscriptionExpired;
use CleaniqueCoders\PackageSubscription\Events\PlanChanged;
use CleaniqueCoders\PackageSubscription\Events\UsageLimitExceeded;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SubscriptionCreated::class => [
            \App\Listeners\SendWelcomeEmail::class,
            \App\Listeners\SetupInitialResources::class,
        ],
        SubscriptionCancelled::class => [
            \App\Listeners\HandleCancellation::class,
        ],
        SubscriptionRenewed::class => [
            \App\Listeners\SendRenewalReceipt::class,
        ],
        SubscriptionExpired::class => [
            \App\Listeners\HandleExpiration::class,
            \App\Listeners\CleanupUserResources::class,
        ],
        PlanChanged::class => [
            \App\Listeners\HandlePlanChange::class,
        ],
        UsageLimitExceeded::class => [
            \App\Listeners\SendUpgradeReminder::class,
        ],
    ];
}
```

### Queueable Listeners

```php
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWelcomeEmail implements ShouldQueue
{
    public $queue = 'notifications';

    public function handle(SubscriptionCreated $event): void
    {
        // This will run asynchronously
    }
}
```

---

## Event Subscriber

Group related listeners in a subscriber:

```php
<?php

namespace App\Listeners;

use CleaniqueCoders\PackageSubscription\Events\SubscriptionCreated;
use CleaniqueCoders\PackageSubscription\Events\SubscriptionCancelled;
use CleaniqueCoders\PackageSubscription\Events\SubscriptionRenewed;
use Illuminate\Events\Dispatcher;

class SubscriptionEventSubscriber
{
    public function handleCreated(SubscriptionCreated $event): void
    {
        // Handle created
    }

    public function handleCancelled(SubscriptionCancelled $event): void
    {
        // Handle cancelled
    }

    public function handleRenewed(SubscriptionRenewed $event): void
    {
        // Handle renewed
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            SubscriptionCreated::class => 'handleCreated',
            SubscriptionCancelled::class => 'handleCancelled',
            SubscriptionRenewed::class => 'handleRenewed',
        ];
    }
}
```

Register in EventServiceProvider:

```php
protected $subscribe = [
    \App\Listeners\SubscriptionEventSubscriber::class,
];
```
