# Data Models

## Database Schema

The package creates four database tables to manage subscriptions:

```
┌──────────────┐       ┌──────────────────┐
│    plans     │       │  subscriptions   │
├──────────────┤       ├──────────────────┤
│ id           │───┐   │ id               │
│ uuid         │   │   │ uuid             │
│ slug         │   └──►│ plan_id          │
│ name         │       │ subscribable_type│
│ description  │       │ subscribable_id  │
│ price        │       │ status           │
│ billing_     │       │ trial_ends_at    │
│   period     │       │ starts_at        │
│ billing_     │       │ ends_at          │
│   interval   │       │ cancelled_at     │
│ trial_period │       │ suspended_at     │
│   _days      │       │ grace_ends_at    │
│ grace_period │       │ price            │
│   _days      │       │ billing_period   │
│ features     │       │ snapshot         │
│ metadata     │       │ metadata         │
│ is_active    │       │ created_at       │
│ sort_order   │       │ updated_at       │
│ created_at   │       │ deleted_at       │
│ updated_at   │       └──────────────────┘
│ deleted_at   │               │
└──────────────┘               │
                               ▼
┌──────────────┐       ┌──────────────────────────┐
│    usages    │       │  subscription_history    │
├──────────────┤       ├──────────────────────────┤
│ id           │       │ id                       │
│ subscription │◄──────│ subscription_id          │
│   _id        │       │ event_type               │
│ feature      │       │ from_plan_id             │
│ used         │       │ to_plan_id               │
│ limit        │       │ metadata                 │
│ reset_at     │       │ created_at               │
│ created_at   │       │ updated_at               │
│ updated_at   │       └──────────────────────────┘
└──────────────┘
```

## Plan Model

The `Plan` model represents subscription tiers.

### Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `id` | int | Primary key |
| `uuid` | string | Unique identifier |
| `slug` | string | URL-friendly identifier |
| `name` | string | Display name |
| `description` | string | Plan description |
| `price` | decimal | Price per billing period |
| `billing_period` | BillingPeriod | Billing cycle (monthly, yearly, etc.) |
| `billing_interval` | int | Number of periods per cycle |
| `trial_period_days` | int | Trial period length in days |
| `grace_period_days` | int | Grace period length in days |
| `features` | array | Key-value feature definitions |
| `metadata` | array | Custom metadata |
| `is_active` | bool | Whether plan is available |
| `sort_order` | int | Display ordering |

### Relationships

```php
// Get all subscriptions for this plan
$plan->subscriptions();

// Get only active subscriptions
$plan->activeSubscriptions();
```

### Key Methods

```php
// Check if plan has a feature
$plan->hasFeature('api_calls'); // true/false

// Get feature value
$plan->getFeatureValue('storage'); // 50

// Get feature limit as integer
$plan->getFeatureLimit('projects'); // 10

// Check if feature is enabled (boolean)
$plan->isFeatureEnabled('custom_domain'); // true/false

// Check if free plan
$plan->isFree(); // true/false

// Check if has trial
$plan->hasTrial(); // true/false

// Calculate next billing date
$plan->calculateNextBillingDate(); // Carbon instance
```

## Subscription Model

The `Subscription` model represents an active subscription.

### Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `id` | int | Primary key |
| `uuid` | string | Unique identifier |
| `subscribable_type` | string | Polymorphic model type |
| `subscribable_id` | int | Polymorphic model ID |
| `plan_id` | int | Associated plan |
| `status` | SubscriptionStatus | Current status |
| `trial_ends_at` | datetime | Trial period end |
| `starts_at` | datetime | Subscription start |
| `ends_at` | datetime | Current period end |
| `cancelled_at` | datetime | Cancellation timestamp |
| `suspended_at` | datetime | Suspension timestamp |
| `grace_ends_at` | datetime | Grace period end |
| `price` | decimal | Locked-in price |
| `billing_period` | string | Billing cycle |
| `snapshot` | array | Frozen plan features |
| `metadata` | array | Custom metadata |

### Relationships

```php
// Get the subscribable (User, Team, etc.)
$subscription->subscribable();

// Get the plan
$subscription->plan();

// Get usage records
$subscription->usages();

// Get history records
$subscription->history();
```

### Status Checking Methods

```php
$subscription->isActive();       // Currently active
$subscription->isCancelled();    // Has been cancelled
$subscription->isSuspended();    // Is suspended
$subscription->isExpired();      // Has expired
$subscription->isOnTrial();      // In trial period
$subscription->isOnGracePeriod(); // In grace period
$subscription->hasEnded();       // Period has ended
```

### Lifecycle Methods

```php
// Cancel subscription
$subscription->cancel();           // At period end
$subscription->cancel(true);       // Immediately

// Suspend and resume
$subscription->suspend('Payment failed');
$subscription->resume();

// Renew subscription
$subscription->renew();

// Change plans
$subscription->switchTo($newPlan);
$subscription->upgradeTo($newPlan);
$subscription->downgradeTo($newPlan);
```

## Usage Model

The `Usage` model tracks feature consumption.

### Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `id` | int | Primary key |
| `subscription_id` | int | Associated subscription |
| `feature` | string | Feature identifier |
| `used` | float | Amount consumed |
| `limit` | float | Feature limit (cached) |
| `reset_at` | datetime | Last reset timestamp |

### Key Methods

```php
// Check if within limit
$usage->isWithinLimit(); // true/false

// Check if exceeded
$usage->isExceeded(); // true/false

// Get remaining
$usage->remaining(); // float

// Get percentage used
$usage->percentage(); // 0-100

// Increment/decrement
$usage->incrementUsage(5);
$usage->decrementUsage(2);
```

## SubscriptionHistory Model

The `SubscriptionHistory` model tracks subscription events.

### Attributes

| Attribute | Type | Description |
|-----------|------|-------------|
| `id` | int | Primary key |
| `subscription_id` | int | Associated subscription |
| `event_type` | string | Event type (created, renewed, etc.) |
| `from_plan_id` | int | Previous plan (for changes) |
| `to_plan_id` | int | New plan |
| `metadata` | array | Event metadata |

## Query Scopes

### Plan Scopes

```php
// Get active plans
Plan::active()->get();

// Get ordered plans
Plan::ordered()->get();

// Combined
Plan::active()->ordered()->get();
```

### Subscription Scopes

```php
// Active subscriptions
Subscription::active()->get();

// On trial
Subscription::onTrial()->get();

// Cancelled
Subscription::cancelled()->get();

// Expired
Subscription::expired()->get();

// For specific plan
Subscription::forPlan('pro')->get();
Subscription::forPlan($planInstance)->get();
```
