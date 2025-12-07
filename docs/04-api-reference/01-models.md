# Models

## Plan

`CleaniqueCoders\PackageSubscription\Models\Plan`

Represents a subscription plan with pricing, features, and limits.

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `id` | int | Primary key |
| `uuid` | string | UUID identifier |
| `slug` | string | URL-friendly identifier |
| `name` | string | Display name |
| `description` | string\|null | Plan description |
| `price` | float | Price per billing period |
| `billing_period` | BillingPeriod | Billing cycle enum |
| `billing_interval` | int | Number of periods |
| `trial_period_days` | int | Trial length in days |
| `grace_period_days` | int | Grace period length |
| `features` | array | Feature definitions |
| `metadata` | array\|null | Custom metadata |
| `is_active` | bool | Whether plan is available |
| `sort_order` | int | Display ordering |
| `created_at` | Carbon | Creation timestamp |
| `updated_at` | Carbon | Last update timestamp |
| `deleted_at` | Carbon\|null | Soft delete timestamp |

### Relationships

```php
// Get all subscriptions for this plan
$plan->subscriptions(): HasMany

// Get active subscriptions only
$plan->activeSubscriptions(): HasMany
```

### Query Scopes

```php
// Active plans only
Plan::active()->get();

// Ordered by sort_order and price
Plan::ordered()->get();
```

### Methods

```php
// Feature checking
$plan->hasFeature(string $feature): bool
$plan->getFeatureValue(string $feature): mixed
$plan->getFeatureLimit(string $feature): ?int
$plan->isFeatureEnabled(string $feature): bool
$plan->getAllFeatures(): array

// Plan type checking
$plan->isFree(): bool
$plan->hasTrial(): bool
$plan->isLifetime(): bool

// Billing calculation
$plan->calculateNextBillingDate(?Carbon $from = null): Carbon

// Formatted price accessor
$plan->formatted_price // "29.99"
```

---

## Subscription

`CleaniqueCoders\PackageSubscription\Models\Subscription`

Represents an active subscription linking a subscribable to a plan.

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `id` | int | Primary key |
| `uuid` | string | UUID identifier |
| `subscribable_type` | string | Polymorphic model type |
| `subscribable_id` | int | Polymorphic model ID |
| `plan_id` | int | Associated plan ID |
| `status` | SubscriptionStatus | Current status |
| `trial_ends_at` | Carbon\|null | Trial end date |
| `starts_at` | Carbon | Subscription start |
| `ends_at` | Carbon | Current period end |
| `cancelled_at` | Carbon\|null | Cancellation timestamp |
| `suspended_at` | Carbon\|null | Suspension timestamp |
| `grace_ends_at` | Carbon\|null | Grace period end |
| `price` | float | Locked-in price |
| `billing_period` | string | Billing cycle |
| `snapshot` | array\|null | Frozen plan features |
| `metadata` | array\|null | Custom metadata |
| `created_at` | Carbon | Creation timestamp |
| `updated_at` | Carbon | Last update timestamp |
| `deleted_at` | Carbon\|null | Soft delete timestamp |

### Relationships

```php
// Get the subscribable (User, Team, etc.)
$subscription->subscribable(): MorphTo

// Get the plan
$subscription->plan(): BelongsTo

// Get usage records
$subscription->usages(): HasMany

// Get history records
$subscription->history(): HasMany
```

### Query Scopes

```php
Subscription::active()->get();          // Active + on_trial
Subscription::onTrial()->get();         // On trial only
Subscription::cancelled()->get();       // Cancelled only
Subscription::expired()->get();         // Expired only
Subscription::forPlan('pro')->get();    // For specific plan
Subscription::forPlan($plan)->get();    // For plan instance
```

### Status Methods

```php
$subscription->isActive(): bool         // Active or on trial, not ended
$subscription->isCancelled(): bool      // Status is cancelled
$subscription->isSuspended(): bool      // Status is suspended
$subscription->isExpired(): bool        // Status is expired or ended
$subscription->isOnTrial(): bool        // On trial and trial not ended
$subscription->isOnGracePeriod(): bool  // In grace period
$subscription->hasEnded(): bool         // ends_at is in past
```

### Lifecycle Methods

```php
// Cancel
$subscription->cancel(bool $immediately = false): self
$subscription->cancelAtPeriodEnd(): self

// Suspend/Resume
$subscription->suspend(?string $reason = null): self
$subscription->resume(): self

// Renew
$subscription->renew(): self

// Expire
$subscription->expire(): self

// Plan changes
$subscription->switchTo(Plan $plan, array $options = []): self
$subscription->upgradeTo(Plan $plan): self
$subscription->downgradeTo(Plan $plan): self
```

### Feature Methods

```php
$subscription->canUseFeature(string $feature): bool
$subscription->getFeatureValue(string $feature): mixed
$subscription->getFeatureLimit(string $feature): ?int
```

### Usage Methods

```php
$subscription->recordUsage(string $feature, float $amount): ?Usage
$subscription->incrementUsage(string $feature, float $amount = 1): ?Usage
$subscription->decrementUsage(string $feature, float $amount = 1): ?Usage
$subscription->getUsage(string $feature): float
$subscription->resetUsage(?string $feature = null): void
```

---

## Usage

`CleaniqueCoders\PackageSubscription\Models\Usage`

Tracks feature usage for a subscription.

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `id` | int | Primary key |
| `subscription_id` | int | Associated subscription |
| `feature` | string | Feature identifier |
| `used` | float | Amount consumed |
| `limit` | float | Feature limit (cached) |
| `reset_at` | Carbon\|null | Last reset timestamp |
| `created_at` | Carbon | Creation timestamp |
| `updated_at` | Carbon | Last update timestamp |

### Relationships

```php
$usage->subscription(): BelongsTo
```

### Methods

```php
// Check limits
$usage->isWithinLimit(): bool
$usage->isExceeded(): bool

// Get amounts
$usage->remaining(): float      // limit - used
$usage->percentage(): float     // (used / limit) * 100

// Modify usage
$usage->incrementUsage(float $amount = 1): self
$usage->decrementUsage(float $amount = 1): self
```

---

## SubscriptionHistory

`CleaniqueCoders\PackageSubscription\Models\SubscriptionHistory`

Records subscription lifecycle events.

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `id` | int | Primary key |
| `subscription_id` | int | Associated subscription |
| `event_type` | string | Event type identifier |
| `from_plan_id` | int\|null | Previous plan (for changes) |
| `to_plan_id` | int\|null | New plan |
| `metadata` | array\|null | Event metadata |
| `created_at` | Carbon | Event timestamp |
| `updated_at` | Carbon | Last update timestamp |

### Event Types

| Type | Description |
|------|-------------|
| `created` | Subscription created |
| `renewed` | Subscription renewed |
| `cancelled` | Subscription cancelled |
| `suspended` | Subscription suspended |
| `resumed` | Subscription resumed |
| `expired` | Subscription expired |
| `plan_changed` | Plan upgraded/downgraded |

### Relationships

```php
$history->subscription(): BelongsTo
$history->fromPlan(): BelongsTo
$history->toPlan(): BelongsTo
```

---

## Model Configuration

Customize models in `config/package-subscription.php`:

```php
'models' => [
    'plan' => \CleaniqueCoders\PackageSubscription\Models\Plan::class,
    'subscription' => \CleaniqueCoders\PackageSubscription\Models\Subscription::class,
    'usage' => \CleaniqueCoders\PackageSubscription\Models\Usage::class,
    'history' => \CleaniqueCoders\PackageSubscription\Models\SubscriptionHistory::class,
],
```

### Extending Models

```php
<?php

namespace App\Models;

use CleaniqueCoders\PackageSubscription\Models\Subscription as BaseSubscription;

class Subscription extends BaseSubscription
{
    protected $appends = ['is_premium'];

    public function getIsPremiumAttribute(): bool
    {
        return in_array($this->plan->slug, ['pro', 'enterprise']);
    }
}
```

Update config:

```php
'models' => [
    'subscription' => \App\Models\Subscription::class,
],
```
