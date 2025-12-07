# Package Subscription Management for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cleaniquecoders/package-subscription.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/package-subscription)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/package-subscription/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cleaniquecoders/package-subscription/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/package-subscription/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cleaniquecoders/package-subscription/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/cleaniquecoders/package-subscription.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/package-subscription)

A comprehensive Laravel package for managing subscription plans and subscriptions in SaaS applications. This package provides an easy-to-configure solution for implementing package-based subscription management, allowing you to create flexible pricing tiers, manage user subscriptions, track usage, and handle the complete subscription lifecycle.

## Features

### Plan Management

- **Flexible Plan Configuration**: Create unlimited subscription plans with custom pricing, features, and limits
- **Plan Tiers**: Support for multiple pricing tiers (Free, Basic, Pro, Enterprise, etc.)
- **Feature Toggles**: Define feature access per plan for granular control
- **Usage Limits**: Set quotas and limits for each plan (API calls, storage, users, etc.)
- **Custom Metadata**: Attach custom attributes to plans for extended functionality

### Subscription Lifecycle

- **Subscribe**: Easy subscription creation with plan assignment
- **Renew**: Automatic and manual subscription renewal handling
- **Cancel**: Graceful subscription cancellation with end-of-period access
- **Suspend/Resume**: Temporarily pause and reactivate subscriptions
- **Trial Periods**: Configurable trial periods for new subscriptions
- **Grace Periods**: Allow access continuation during payment issues

### Billing & Payments

- **Multiple Billing Periods**: Support for monthly, quarterly, yearly, and custom billing cycles
- **Proration**: Handle mid-cycle upgrades/downgrades with automatic proration
- **Metered Billing**: Track and bill based on usage metrics
- **Invoice Generation**: Generate subscription invoices and receipts
- **Payment Gateway Agnostic**: Works with any payment provider

### Plan Changes

- **Upgrades**: Seamless plan upgrades with immediate feature access
- **Downgrades**: Controlled downgrades with policy enforcement
- **Plan Switching**: Change between plans with customizable behavior
- **Proration Calculation**: Automatic credit/debit calculation for mid-cycle changes

### Usage Tracking

- **Usage Monitoring**: Track feature usage against plan limits
- **Quota Management**: Enforce and monitor quotas in real-time
- **Usage Reports**: Generate usage statistics and reports
- **Overage Handling**: Define behavior when limits are exceeded
- **Reset Cycles**: Automatic usage counter resets per billing period

### Multi-Tenancy

- **Team/Organization Support**: Manage subscriptions per team or organization
- **User-based Subscriptions**: Individual user subscriptions
- **Flexible Ownership**: Support for various subscription ownership models

### Events & Webhooks

- **Subscription Events**: Fire events on subscription lifecycle changes
- **Usage Events**: Track usage-related events
- **Custom Hooks**: Extend functionality with custom event listeners
- **Integration Ready**: Easy integration with notification systems

### Developer Experience

- **Fluent API**: Intuitive, chainable methods for common operations
- **Eloquent Models**: Fully integrated with Laravel's Eloquent ORM
- **Query Scopes**: Pre-built query scopes for common filtering
- **Trait-based**: Easy integration with existing User/Team models
- **Comprehensive Tests**: Well-tested codebase with high coverage
- **Type-safe**: Full PHPStan Level 5 compliance

### Access Control

- **Feature Gates**: Check feature access based on active subscription
- **Middleware**: Protect routes based on subscription status
- **Blade Directives**: Conditionally render views based on plan features
- **API Throttling**: Rate limiting based on subscription tier

## Installation

You can install the package via composer:

```bash
composer require cleaniquecoders/package-subscription
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="package-subscription-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="package-subscription-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="package-subscription-views"
```

## Usage

### Setting Up Models

Add the `HasSubscriptions` trait to your User or Team model:

```php
use CleaniqueCoders\PackageSubscription\Traits\HasSubscriptions;

class User extends Authenticatable
{
    use HasSubscriptions;

    // Your model code...
}
```

### Creating Plans

```php
use CleaniqueCoders\PackageSubscription\Models\Plan;

// Create a basic plan
$basicPlan = Plan::create([
    'name' => 'Basic',
    'slug' => 'basic',
    'description' => 'Perfect for individuals',
    'price' => 9.99,
    'billing_period' => 'monthly',
    'features' => [
        'projects' => 10,
        'storage' => 5, // GB
        'api_calls' => 1000,
        'support' => 'email',
    ],
]);

// Create a pro plan
$proPlan = Plan::create([
    'name' => 'Pro',
    'slug' => 'pro',
    'description' => 'For growing teams',
    'price' => 29.99,
    'billing_period' => 'monthly',
    'trial_period_days' => 14,
    'features' => [
        'projects' => 50,
        'storage' => 50, // GB
        'api_calls' => 10000,
        'support' => 'priority',
        'custom_domain' => true,
    ],
]);
```

### Managing Subscriptions

```php
// Subscribe a user to a plan
$user->subscribeTo($basicPlan);

// Subscribe with trial period
$user->subscribeTo($proPlan, [
    'trial_ends_at' => now()->addDays(14),
]);

// Check if user has an active subscription
if ($user->hasActiveSubscription()) {
    // User is subscribed
}

// Check if user is subscribed to a specific plan
if ($user->subscribedTo('pro')) {
    // User is on the pro plan
}

// Get active subscription
$subscription = $user->activeSubscription();

// Cancel subscription
$user->activeSubscription()->cancel();

// Cancel at end of billing period
$user->activeSubscription()->cancelAtPeriodEnd();

// Resume a cancelled subscription
$user->activeSubscription()->resume();
```

### Plan Upgrades & Downgrades

```php
// Upgrade to a higher plan
$user->activeSubscription()->upgradeTo($proPlan);

// Downgrade to a lower plan
$user->activeSubscription()->downgradeTo($basicPlan);

// Switch plans with custom options
$user->activeSubscription()->switchTo($proPlan, [
    'prorate' => true,
    'invoice_now' => false,
]);
```

### Feature Access Control

```php
// Check if user has access to a feature
if ($user->canUseFeature('custom_domain')) {
    // Feature is available in their plan
}

// Get feature limit
$projectLimit = $user->getFeatureLimit('projects'); // 10

// Check if within limits
if ($user->withinLimit('projects', $currentProjectCount)) {
    // User has not exceeded their project limit
}
```

### Usage Tracking

```php
// Record usage
$user->recordUsage('api_calls', 100);

// Get current usage
$apiUsage = $user->getUsage('api_calls');

// Check if usage exceeds limit
if ($user->exceedsLimit('api_calls')) {
    // User has exceeded their API call limit
}

// Get usage percentage
$percentage = $user->getUsagePercentage('storage'); // 0-100
```

### Middleware Protection

Protect routes based on subscription status:

```php
// In routes/web.php
Route::middleware(['subscribed'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

// Require specific plan
Route::middleware(['subscribed:pro'])->group(function () {
    Route::get('/advanced-features', [AdvancedController::class, 'index']);
});

// Require feature access
Route::middleware(['feature:custom_domain'])->group(function () {
    Route::get('/domain-settings', [DomainController::class, 'index']);
});
```

### Blade Directives

```blade
@subscribed
    <p>You have an active subscription!</p>
@else
    <a href="/pricing">Subscribe Now</a>
@endsubscribed

@subscribedToPlan('pro')
    <a href="/advanced-features">Access Pro Features</a>
@endsubscribedToPlan

@feature('custom_domain')
    <a href="/domain-settings">Configure Custom Domain</a>
@endfeature
```

### Handling Events

Listen to subscription events:

```php
// In EventServiceProvider.php
use CleaniqueCoders\PackageSubscription\Events\SubscriptionCreated;
use CleaniqueCoders\PackageSubscription\Events\SubscriptionCancelled;
use CleaniqueCoders\PackageSubscription\Events\SubscriptionRenewed;

protected $listen = [
    SubscriptionCreated::class => [
        SendWelcomeEmail::class,
    ],
    SubscriptionCancelled::class => [
        SendCancellationEmail::class,
    ],
    SubscriptionRenewed::class => [
        SendRenewalReceipt::class,
    ],
];
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Nasrul Hazim Bin Mohamad](https://github.com/nasrulhazim)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
