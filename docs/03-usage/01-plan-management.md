# Plan Management

## Creating Plans

Use the `Plan` model to create subscription plans:

```php
use CleaniqueCoders\PackageSubscription\Models\Plan;
use CleaniqueCoders\PackageSubscription\Enums\BillingPeriod;

// Create a basic plan
$plan = Plan::create([
    'name' => 'Basic',
    'slug' => 'basic',
    'description' => 'Perfect for individuals',
    'price' => 9.99,
    'billing_period' => BillingPeriod::MONTHLY,
    'billing_interval' => 1,
    'trial_period_days' => 0,
    'grace_period_days' => 3,
    'is_active' => true,
    'sort_order' => 1,
    'features' => [
        'projects' => 10,
        'storage' => 5,
        'api_calls' => 1000,
        'support' => 'email',
    ],
]);
```

## Plan Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `name` | string | Display name |
| `slug` | string | URL-friendly identifier (auto-generated from name) |
| `description` | string | Plan description |
| `price` | decimal | Price per billing period |
| `billing_period` | BillingPeriod | Billing cycle enum |
| `billing_interval` | int | Number of periods per cycle |
| `trial_period_days` | int | Trial period length (0 = no trial) |
| `grace_period_days` | int | Grace period after expiry |
| `features` | array | Feature definitions |
| `metadata` | array | Custom metadata |
| `is_active` | bool | Whether plan is available |
| `sort_order` | int | Display ordering |

## Billing Periods

Available billing periods:

```php
use CleaniqueCoders\PackageSubscription\Enums\BillingPeriod;

BillingPeriod::DAILY;      // Daily billing
BillingPeriod::WEEKLY;     // Weekly billing
BillingPeriod::MONTHLY;    // Monthly billing
BillingPeriod::QUARTERLY;  // Every 3 months
BillingPeriod::YEARLY;     // Yearly billing
BillingPeriod::LIFETIME;   // One-time purchase
```

## Defining Features

Features are defined as key-value pairs in the `features` array:

### Numeric Limits

```php
'features' => [
    'projects' => 10,       // Max 10 projects
    'storage' => 50,        // 50 GB storage
    'api_calls' => 10000,   // 10,000 API calls/month
    'team_members' => 5,    // Max 5 team members
],
```

### Boolean Features

```php
'features' => [
    'custom_domain' => true,    // Feature enabled
    'priority_support' => true,
    'white_label' => false,     // Feature disabled
],
```

### String Features

```php
'features' => [
    'support' => 'email',       // Email support only
    'support' => 'priority',    // Priority support
    'support' => '24/7',        // 24/7 support
],
```

### Mixed Features

```php
'features' => [
    'projects' => 50,           // Numeric limit
    'storage' => 100,           // Numeric limit
    'custom_domain' => true,    // Boolean feature
    'support' => 'priority',    // String feature
    'api_calls' => -1,          // -1 for unlimited
],
```

## Plan Examples

### Free Tier

```php
Plan::create([
    'name' => 'Free',
    'slug' => 'free',
    'description' => 'Get started for free',
    'price' => 0,
    'billing_period' => BillingPeriod::MONTHLY,
    'is_active' => true,
    'sort_order' => 0,
    'features' => [
        'projects' => 3,
        'storage' => 1,
        'api_calls' => 100,
        'support' => 'community',
    ],
]);
```

### Pro Tier

```php
Plan::create([
    'name' => 'Pro',
    'slug' => 'pro',
    'description' => 'For growing teams',
    'price' => 29.99,
    'billing_period' => BillingPeriod::MONTHLY,
    'trial_period_days' => 14,
    'grace_period_days' => 3,
    'is_active' => true,
    'sort_order' => 1,
    'features' => [
        'projects' => 50,
        'storage' => 50,
        'api_calls' => 10000,
        'custom_domain' => true,
        'priority_support' => true,
        'support' => 'email',
    ],
]);
```

### Enterprise Tier

```php
Plan::create([
    'name' => 'Enterprise',
    'slug' => 'enterprise',
    'description' => 'For large organizations',
    'price' => 199.99,
    'billing_period' => BillingPeriod::MONTHLY,
    'trial_period_days' => 30,
    'grace_period_days' => 7,
    'is_active' => true,
    'sort_order' => 2,
    'features' => [
        'projects' => -1,       // Unlimited
        'storage' => 500,
        'api_calls' => -1,      // Unlimited
        'custom_domain' => true,
        'white_label' => true,
        'sso' => true,
        'support' => '24/7',
    ],
]);
```

### Yearly Plan

```php
Plan::create([
    'name' => 'Pro Yearly',
    'slug' => 'pro-yearly',
    'description' => 'Pro plan billed yearly (save 20%)',
    'price' => 287.90,  // 29.99 * 12 * 0.8
    'billing_period' => BillingPeriod::YEARLY,
    'trial_period_days' => 14,
    'is_active' => true,
    'features' => [
        'projects' => 50,
        'storage' => 50,
        'api_calls' => 10000,
        'custom_domain' => true,
    ],
]);
```

### Lifetime Plan

```php
Plan::create([
    'name' => 'Lifetime',
    'slug' => 'lifetime',
    'description' => 'One-time purchase, forever access',
    'price' => 499.00,
    'billing_period' => BillingPeriod::LIFETIME,
    'is_active' => true,
    'features' => [
        'projects' => -1,
        'storage' => 100,
        'api_calls' => -1,
        'custom_domain' => true,
    ],
]);
```

## Querying Plans

### Get Active Plans

```php
// Get all active plans ordered
$plans = Plan::active()->ordered()->get();

// Get specific plan by slug
$plan = Plan::where('slug', 'pro')->first();

// Get free plans
$freePlans = Plan::where('price', 0)->get();

// Get plans with trial
$trialPlans = Plan::where('trial_period_days', '>', 0)->get();
```

### Plan Methods

```php
$plan = Plan::where('slug', 'pro')->first();

// Check features
$plan->hasFeature('custom_domain');     // true
$plan->getFeatureValue('storage');      // 50
$plan->getFeatureLimit('projects');     // 50
$plan->isFeatureEnabled('custom_domain'); // true

// Check plan type
$plan->isFree();      // false
$plan->hasTrial();    // true
$plan->isLifetime();  // false

// Get all features
$plan->getAllFeatures(); // ['projects' => 50, ...]

// Calculate next billing date
$plan->calculateNextBillingDate(); // Carbon: 2024-02-01
```

## Updating Plans

```php
$plan = Plan::where('slug', 'pro')->first();

// Update price
$plan->update(['price' => 34.99]);

// Update features
$plan->update([
    'features' => array_merge($plan->features, [
        'projects' => 100,  // Increase limit
        'analytics' => true, // Add new feature
    ]),
]);

// Deactivate plan
$plan->update(['is_active' => false]);
```

## Plan Metadata

Store additional data using metadata:

```php
Plan::create([
    'name' => 'Pro',
    'slug' => 'pro',
    'price' => 29.99,
    'billing_period' => BillingPeriod::MONTHLY,
    'features' => [...],
    'metadata' => [
        'stripe_price_id' => 'price_1234567890',
        'popular' => true,
        'badge' => 'Most Popular',
        'color' => '#6366f1',
    ],
]);
```

Access metadata:

```php
$plan->metadata['stripe_price_id'];
$plan->getMeta('stripe_price_id');
```

## Deleting Plans

Plans use soft deletes to preserve subscription history:

```php
// Soft delete (recommended)
$plan->delete();

// Check if deleted
$plan->trashed(); // true

// Restore
$plan->restore();

// Force delete (permanently remove)
$plan->forceDelete();
```

> **Note:** Consider deactivating plans (`is_active = false`) instead of deleting them to preserve existing subscriptions.
