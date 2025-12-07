# Getting Started

## Installation

Install the package via Composer:

```bash
composer require cleaniquecoders/package-subscription
```

## Publish Assets

### Migrations (Required)

Publish and run the database migrations:

```bash
php artisan vendor:publish --tag="package-subscription-migrations"
php artisan migrate
```

This creates four tables:

- `plans` - Subscription plan definitions
- `subscriptions` - Active subscriptions
- `usages` - Feature usage tracking
- `subscription_history` - Subscription event history

### Configuration (Optional)

Publish the configuration file:

```bash
php artisan vendor:publish --tag="package-subscription-config"
```

This creates `config/package-subscription.php` with all available options.

### Views (Optional)

Publish the views if you need to customize them:

```bash
php artisan vendor:publish --tag="package-subscription-views"
```

## Setup Your Model

Add the `HasSubscriptions` trait to your User model (or any model that should have subscriptions):

```php
<?php

namespace App\Models;

use CleaniqueCoders\PackageSubscription\Concerns\HasSubscriptions;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasSubscriptions;

    // Your existing model code...
}
```

For team-based subscriptions, add the trait to your Team model:

```php
<?php

namespace App\Models;

use CleaniqueCoders\PackageSubscription\Concerns\HasSubscriptions;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasSubscriptions;
}
```

## Create Your First Plan

Create subscription plans using the `Plan` model:

```php
use CleaniqueCoders\PackageSubscription\Models\Plan;

// Create a free plan
Plan::create([
    'name' => 'Free',
    'slug' => 'free',
    'description' => 'Get started for free',
    'price' => 0,
    'billing_period' => 'monthly',
    'features' => [
        'projects' => 3,
        'storage' => 1, // GB
        'api_calls' => 100,
    ],
]);

// Create a pro plan
Plan::create([
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
        'custom_domain' => true,
        'priority_support' => true,
    ],
]);
```

## Subscribe a User

```php
use CleaniqueCoders\PackageSubscription\Models\Plan;

$user = User::find(1);
$plan = Plan::where('slug', 'pro')->first();

// Subscribe the user
$subscription = $user->subscribeTo($plan);

// Or with options
$subscription = $user->subscribeTo($plan, [
    'trial_days' => 7,
    'metadata' => ['source' => 'homepage'],
]);
```

## Check Subscription Status

```php
// Check if user has active subscription
if ($user->hasActiveSubscription()) {
    // User is subscribed
}

// Check specific plan
if ($user->subscribedTo('pro')) {
    // User is on the Pro plan
}

// Check feature access
if ($user->canUseFeature('custom_domain')) {
    // User has access to custom domains
}

// Get feature limit
$projectLimit = $user->getFeatureLimit('projects'); // 50
```

## Protect Routes

Use the provided middleware to protect routes:

```php
// routes/web.php
use Illuminate\Support\Facades\Route;

// Require any active subscription
Route::middleware(['auth', 'subscribed'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

// Require specific plan
Route::middleware(['auth', 'subscribed.plan:pro'])->group(function () {
    Route::get('/advanced', [AdvancedController::class, 'index']);
});

// Require specific feature
Route::middleware(['auth', 'feature:custom_domain'])->group(function () {
    Route::get('/domains', [DomainController::class, 'index']);
});
```

## Use Blade Directives

```blade
@subscribed
    <p>Welcome, subscriber!</p>
@else
    <a href="/pricing">View Plans</a>
@endsubscribed

@subscribedToPlan('pro')
    <a href="/pro-features">Pro Features</a>
@endsubscribedToPlan

@feature('custom_domain')
    <a href="/domains">Manage Domains</a>
@endfeature
```

## Next Steps

- [Configure the package](./02-configuration.md) to match your needs
- Learn about [usage tracking](../03-usage/03-usage-tracking.md)
- Set up [event listeners](../04-api-reference/04-events.md) for notifications
