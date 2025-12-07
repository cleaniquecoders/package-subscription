# Overview

## Package Structure

The package follows Laravel's standard package structure using Spatie's Laravel Package Tools.

```
src/
├── PackageSubscription.php              # Main facade class
├── PackageSubscriptionServiceProvider.php # Service provider
├── Commands/                            # Artisan commands
│   ├── CheckExpiredSubscriptionsCommand.php
│   ├── RenewSubscriptionsCommand.php
│   └── ResetUsageCommand.php
├── Concerns/                            # Traits
│   └── HasSubscriptions.php
├── Enums/                               # PHP Enums
│   ├── BillingPeriod.php
│   └── SubscriptionStatus.php
├── Events/                              # Event classes
│   ├── PlanChanged.php
│   ├── SubscriptionCancelled.php
│   ├── SubscriptionCreated.php
│   ├── SubscriptionExpired.php
│   ├── SubscriptionRenewed.php
│   ├── SubscriptionResumed.php
│   ├── SubscriptionSuspended.php
│   ├── UsageLimitExceeded.php
│   └── UsageRecorded.php
├── Http/
│   └── Middleware/                      # HTTP middleware
│       ├── Feature.php
│       ├── Subscribed.php
│       └── SubscribedToPlan.php
├── Models/                              # Eloquent models
│   ├── Plan.php
│   ├── Subscription.php
│   ├── SubscriptionHistory.php
│   └── Usage.php
├── Notifications/                       # Notification classes
└── Services/                            # Service classes
    ├── ProrationService.php
    ├── SubscriptionService.php
    └── UsageService.php
```

## Namespace

All classes in this package use the namespace:

```php
CleaniqueCoders\PackageSubscription
```

## Core Concepts

### Subscribable

A "subscribable" is any Eloquent model that can have subscriptions. This is typically your `User` model, but can also be a `Team`, `Organization`, or any other model. The subscribable uses the `HasSubscriptions` trait to gain subscription capabilities.

```php
use CleaniqueCoders\PackageSubscription\Concerns\HasSubscriptions;

class User extends Authenticatable
{
    use HasSubscriptions;
}
```

### Plans

Plans define subscription tiers with:

- **Pricing** - Cost per billing period
- **Billing Period** - Monthly, yearly, etc.
- **Features** - Key-value pairs defining feature access and limits
- **Trial Period** - Optional trial days for new subscribers
- **Grace Period** - Days to continue access after subscription ends

### Subscriptions

Subscriptions link a subscribable to a plan with:

- **Status** - Active, on trial, cancelled, suspended, expired
- **Dates** - Start, end, trial end, cancellation dates
- **Snapshot** - Frozen copy of plan features at subscription time
- **Usage Records** - Tracked feature consumption

### Events

The package dispatches events at key lifecycle points:

| Event | Trigger |
|-------|---------|
| `SubscriptionCreated` | New subscription created |
| `SubscriptionRenewed` | Subscription renewed |
| `SubscriptionCancelled` | Subscription cancelled |
| `SubscriptionSuspended` | Subscription suspended |
| `SubscriptionResumed` | Subscription resumed |
| `SubscriptionExpired` | Subscription expired |
| `PlanChanged` | Plan upgraded or downgraded |
| `UsageRecorded` | Usage recorded for a feature |
| `UsageLimitExceeded` | Feature limit exceeded |

## Service Provider

The `PackageSubscriptionServiceProvider` registers:

- Configuration file
- Database migrations
- Artisan commands
- Middleware aliases
- Blade directives

```php
// Registered middleware aliases
'subscribed'      => Subscribed::class,
'subscribed.plan' => SubscribedToPlan::class,
'feature'         => Feature::class,
```

## Dependencies

The package requires:

- PHP 8.3+
- Laravel 11.x or 12.x
- [cleaniquecoders/traitify](https://github.com/cleaniquecoders/traitify) - For UUID, slug, and metadata traits
- [spatie/laravel-package-tools](https://github.com/spatie/laravel-package-tools) - For package scaffolding
