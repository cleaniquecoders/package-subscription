# Traits

## HasSubscriptions

`CleaniqueCoders\PackageSubscription\Concerns\HasSubscriptions`

Add subscription capabilities to any Eloquent model.

### Usage

```php
<?php

namespace App\Models;

use CleaniqueCoders\PackageSubscription\Concerns\HasSubscriptions;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasSubscriptions;
}
```

Works with any model:

```php
class Team extends Model
{
    use HasSubscriptions;
}

class Organization extends Model
{
    use HasSubscriptions;
}
```

---

## Relationships

### subscriptions()

Get all subscriptions for the model.

```php
public function subscriptions(): MorphMany
```

**Returns:** `MorphMany` - All subscription records

**Example:**

```php
$user->subscriptions;                    // Collection of all subscriptions
$user->subscriptions()->active()->get(); // Active subscriptions only
```

---

## Subscription Methods

### subscribeTo()

Subscribe to a plan.

```php
public function subscribeTo(Plan|string $plan, array $options = []): Subscription
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$plan` | Plan\|string | Plan instance or slug |
| `$options` | array | Subscription options |

**Options:**

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `starts_at` | Carbon | now() | Subscription start date |
| `with_trial` | bool | true | Include trial period |
| `trial_days` | int | Plan's value | Override trial length |
| `metadata` | array | [] | Custom metadata |

**Returns:** `Subscription`

**Example:**

```php
// Basic subscription
$subscription = $user->subscribeTo($plan);

// With options
$subscription = $user->subscribeTo('pro', [
    'with_trial' => true,
    'trial_days' => 7,
    'metadata' => ['source' => 'api'],
]);
```

---

### activeSubscription()

Get the active subscription.

```php
public function activeSubscription(): ?Subscription
```

**Returns:** `Subscription|null`

**Example:**

```php
$subscription = $user->activeSubscription();

if ($subscription) {
    echo $subscription->plan->name;
}
```

---

### subscription()

Alias for `activeSubscription()`.

```php
public function subscription(): ?Subscription
```

---

## Status Checking

### hasActiveSubscription()

Check if the model has an active subscription.

```php
public function hasActiveSubscription(): bool
```

**Returns:** `bool`

---

### hasSubscription()

Alias for `hasActiveSubscription()`.

```php
public function hasSubscription(): bool
```

---

### isSubscribed()

Alias for `hasActiveSubscription()`.

```php
public function isSubscribed(): bool
```

---

### subscribedTo()

Check if subscribed to a specific plan.

```php
public function subscribedTo(Plan|string $plan): bool
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$plan` | Plan\|string | Plan instance or slug |

**Returns:** `bool`

**Example:**

```php
if ($user->subscribedTo('pro')) {
    // User is on the Pro plan
}

if ($user->subscribedTo($enterprisePlan)) {
    // User is on the Enterprise plan
}
```

---

### isSubscribedTo()

Alias for `subscribedTo()`.

```php
public function isSubscribedTo(Plan|string $plan): bool
```

---

### subscribedToPlan()

Check if subscribed to a plan by slug.

```php
public function subscribedToPlan(string $planSlug): bool
```

---

### onTrial()

Check if currently on trial.

```php
public function onTrial(): bool
```

**Returns:** `bool`

---

### onGracePeriod()

Check if currently on grace period.

```php
public function onGracePeriod(): bool
```

**Returns:** `bool`

---

## History

### subscriptionHistory()

Get subscription history.

```php
public function subscriptionHistory(): Collection
```

**Returns:** `Collection` - Collection of subscriptions with plan and history

**Example:**

```php
$history = $user->subscriptionHistory();

foreach ($history as $subscription) {
    echo $subscription->plan->name;
    echo $subscription->status->label();

    foreach ($subscription->history as $event) {
        echo $event->event_type;
    }
}
```

---

## Feature Access

### canUseFeature()

Check if the model can use a specific feature.

```php
public function canUseFeature(string $feature): bool
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$feature` | string | Feature identifier |

**Returns:** `bool`

**Example:**

```php
if ($user->canUseFeature('custom_domain')) {
    // Show domain settings
}
```

---

### hasFeature()

Alias for `canUseFeature()`.

```php
public function hasFeature(string $feature): bool
```

---

### getFeatureValue()

Get the value of a feature.

```php
public function getFeatureValue(string $feature): mixed
```

**Returns:** `mixed` - Feature value (bool, int, string, etc.)

**Example:**

```php
$supportType = $user->getFeatureValue('support'); // 'priority'
$hasSSO = $user->getFeatureValue('sso'); // true
```

---

### getFeatureLimit()

Get the numeric limit for a feature.

```php
public function getFeatureLimit(string $feature): ?int
```

**Returns:** `int|null` - Feature limit or null if not numeric

**Example:**

```php
$projectLimit = $user->getFeatureLimit('projects'); // 50
$storageLimit = $user->getFeatureLimit('storage'); // 100
```

---

## Usage Tracking

### recordUsage()

Record usage for a feature.

```php
public function recordUsage(string $feature, float $amount): ?Usage
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$feature` | string | Feature identifier |
| `$amount` | float | Amount to record |

**Returns:** `Usage|null`

**Example:**

```php
$user->recordUsage('api_calls', 100);
$user->recordUsage('storage', 2.5);
```

---

### incrementUsage()

Increment usage for a feature.

```php
public function incrementUsage(string $feature, float $amount = 1): ?Usage
```

**Example:**

```php
$user->incrementUsage('api_calls');
$user->incrementUsage('storage', 0.5);
```

---

### decrementUsage()

Decrement usage for a feature.

```php
public function decrementUsage(string $feature, float $amount = 1): ?Usage
```

**Example:**

```php
$user->decrementUsage('storage', 1.5);
```

---

### getUsage()

Get current usage for a feature.

```php
public function getUsage(string $feature): float
```

**Returns:** `float`

**Example:**

```php
$apiCalls = $user->getUsage('api_calls'); // 150
```

---

### getUsagePercentage()

Get usage as a percentage of the limit.

```php
public function getUsagePercentage(string $feature): float
```

**Returns:** `float` - Percentage (0-100+)

**Example:**

```php
$percentage = $user->getUsagePercentage('api_calls'); // 15.0
```

---

### getRemainingUsage()

Get remaining usage for a feature.

```php
public function getRemainingUsage(string $feature): ?float
```

**Returns:** `float|null`

**Example:**

```php
$remaining = $user->getRemainingUsage('api_calls'); // 850
```

---

## Limit Checking

### withinLimit()

Check if usage is within the limit.

```php
public function withinLimit(string $feature, ?float $current = null): bool
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$feature` | string | Feature identifier |
| `$current` | float\|null | Current count (optional, uses recorded usage) |

**Returns:** `bool`

**Example:**

```php
// Check against recorded usage
if ($user->withinLimit('api_calls')) {
    // Process request
}

// Check with specific count
if ($user->withinLimit('projects', $user->projects()->count())) {
    // Can create project
}
```

---

### exceedsLimit()

Check if usage exceeds the limit.

```php
public function exceedsLimit(string $feature, ?float $current = null): bool
```

**Returns:** `bool`

**Example:**

```php
if ($user->exceedsLimit('api_calls')) {
    return response()->json(['error' => 'Rate limit exceeded'], 429);
}
```

---

## Complete Example

```php
use App\Models\User;
use CleaniqueCoders\PackageSubscription\Models\Plan;

// Create subscription
$user = User::find(1);
$plan = Plan::where('slug', 'pro')->first();

$subscription = $user->subscribeTo($plan, [
    'with_trial' => true,
    'metadata' => ['source' => 'checkout'],
]);

// Check status
$user->hasActiveSubscription(); // true
$user->subscribedTo('pro');     // true
$user->onTrial();               // true

// Feature access
$user->canUseFeature('custom_domain'); // true
$user->getFeatureLimit('projects');    // 50

// Track usage
$user->incrementUsage('api_calls');
$user->getUsage('api_calls');          // 1
$user->exceedsLimit('api_calls');      // false

// Lifecycle
$subscription->cancel();
$subscription->renew();
$subscription->switchTo($newPlan);
```
