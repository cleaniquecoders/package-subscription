# Configuration

The package configuration file is located at `config/package-subscription.php` after publishing.

## Models

Specify custom model classes if you need to extend the default models:

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

use CleaniqueCoders\PackageSubscription\Models\Plan as BasePlan;

class Plan extends BasePlan
{
    // Add custom methods or attributes
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }
}
```

Then update your config:

```php
'models' => [
    'plan' => \App\Models\Plan::class,
    // ...
],
```

## Database Tables

Customize table names if needed:

```php
'tables' => [
    'plans' => 'plans',
    'subscriptions' => 'subscriptions',
    'usages' => 'usages',
    'subscription_history' => 'subscription_history',
],
```

## Proration Settings

Configure how proration is calculated for mid-cycle plan changes:

```php
'proration' => [
    'enabled' => true,       // Enable/disable proration
    'rounding' => 2,         // Decimal places for rounding
    'mode' => 'daily',       // 'daily' or 'hourly' calculation
],
```

| Mode | Description |
|------|-------------|
| `daily` | Calculate based on remaining days (less precise, simpler) |
| `hourly` | Calculate based on remaining hours (more precise) |

## Trial Period

Configure default trial period behavior:

```php
'trial' => [
    'enabled' => true,               // Enable trials globally
    'default_days' => 14,            // Default trial length
    'require_payment_method' => false, // Require payment upfront
],
```

Trial periods can also be set per-plan via the `trial_period_days` attribute.

## Grace Period

Configure grace period behavior after subscription ends:

```php
'grace_period' => [
    'enabled' => true,       // Enable grace periods
    'default_days' => 3,     // Default grace period length
],
```

Grace periods allow continued access while resolving payment issues.

## Usage Tracking

Configure usage tracking behavior:

```php
'usage' => [
    'enabled' => true,           // Enable usage tracking
    'reset_on_renewal' => true,  // Reset usage on subscription renewal
    'track_overage' => true,     // Continue tracking beyond limits
],
```

## Renewal Settings

Configure automatic renewal behavior:

```php
'renewal' => [
    'auto_renew' => true,            // Enable automatic renewal
    'notify_before_days' => 7,       // Days before to send notification
    'retry_failed_renewals' => true, // Retry failed payments
    'retry_attempts' => 3,           // Number of retry attempts
],
```

## Redirects

Configure redirect routes for subscription-related issues:

```php
'redirect' => [
    'no_subscription' => 'home',    // No active subscription
    'wrong_plan' => 'home',         // Not on required plan
    'no_feature' => 'home',         // Feature not available
    'expired' => 'home',            // Subscription expired
],
```

These are route names used by the middleware.

## Middleware

Configure which middleware classes to use:

```php
'middleware' => [
    'subscribed' => \CleaniqueCoders\PackageSubscription\Http\Middleware\Subscribed::class,
    'subscribed.plan' => \CleaniqueCoders\PackageSubscription\Http\Middleware\SubscribedToPlan::class,
    'feature' => \CleaniqueCoders\PackageSubscription\Http\Middleware\Feature::class,
],
```

You can replace these with custom middleware classes if needed.

## Events

Configure event dispatching:

```php
'events' => [
    'dispatch' => true,   // Enable/disable event dispatching
    'queue' => false,     // Queue event handlers
],
```

## Full Configuration Example

```php
<?php

return [
    'models' => [
        'plan' => \CleaniqueCoders\PackageSubscription\Models\Plan::class,
        'subscription' => \CleaniqueCoders\PackageSubscription\Models\Subscription::class,
        'usage' => \CleaniqueCoders\PackageSubscription\Models\Usage::class,
        'history' => \CleaniqueCoders\PackageSubscription\Models\SubscriptionHistory::class,
    ],

    'tables' => [
        'plans' => 'plans',
        'subscriptions' => 'subscriptions',
        'usages' => 'usages',
        'subscription_history' => 'subscription_history',
    ],

    'proration' => [
        'enabled' => true,
        'rounding' => 2,
        'mode' => 'daily',
    ],

    'trial' => [
        'enabled' => true,
        'default_days' => 14,
        'require_payment_method' => false,
    ],

    'grace_period' => [
        'enabled' => true,
        'default_days' => 3,
    ],

    'usage' => [
        'enabled' => true,
        'reset_on_renewal' => true,
        'track_overage' => true,
    ],

    'renewal' => [
        'auto_renew' => true,
        'notify_before_days' => 7,
        'retry_failed_renewals' => true,
        'retry_attempts' => 3,
    ],

    'redirect' => [
        'no_subscription' => 'home',
        'wrong_plan' => 'home',
        'no_feature' => 'home',
        'expired' => 'home',
    ],

    'middleware' => [
        'subscribed' => \CleaniqueCoders\PackageSubscription\Http\Middleware\Subscribed::class,
        'subscribed.plan' => \CleaniqueCoders\PackageSubscription\Http\Middleware\SubscribedToPlan::class,
        'feature' => \CleaniqueCoders\PackageSubscription\Http\Middleware\Feature::class,
    ],

    'events' => [
        'dispatch' => true,
        'queue' => false,
    ],
];
```

## Environment Variables

You can use environment variables for sensitive or environment-specific settings:

```php
'trial' => [
    'enabled' => env('SUBSCRIPTION_TRIAL_ENABLED', true),
    'default_days' => env('SUBSCRIPTION_TRIAL_DAYS', 14),
],
```

Add to your `.env`:

```env
SUBSCRIPTION_TRIAL_ENABLED=true
SUBSCRIPTION_TRIAL_DAYS=7
```
