# Enums

## BillingPeriod

`CleaniqueCoders\PackageSubscription\Enums\BillingPeriod`

Represents billing cycle options for subscription plans.

### Values

| Case | Value | Description |
|------|-------|-------------|
| `DAILY` | `'daily'` | Daily billing |
| `WEEKLY` | `'weekly'` | Weekly billing |
| `MONTHLY` | `'monthly'` | Monthly billing |
| `QUARTERLY` | `'quarterly'` | Quarterly (3 months) |
| `YEARLY` | `'yearly'` | Yearly billing |
| `LIFETIME` | `'lifetime'` | One-time purchase |

### Usage

```php
use CleaniqueCoders\PackageSubscription\Enums\BillingPeriod;

// Creating a plan
Plan::create([
    'name' => 'Pro Monthly',
    'price' => 29.99,
    'billing_period' => BillingPeriod::MONTHLY,
]);

// Comparison
if ($plan->billing_period === BillingPeriod::YEARLY) {
    // Apply yearly discount
}

// Get string value
$value = BillingPeriod::MONTHLY->value; // 'monthly'
```

### Methods

#### interval()

Get the interval value in days.

```php
public function interval(): int
```

**Returns:**

| Period | Days |
|--------|------|
| DAILY | 1 |
| WEEKLY | 7 |
| MONTHLY | 30 |
| QUARTERLY | 90 |
| YEARLY | 365 |
| LIFETIME | 0 |

**Example:**

```php
BillingPeriod::MONTHLY->interval(); // 30
BillingPeriod::YEARLY->interval();  // 365
```

---

#### label()

Get human-readable label.

```php
public function label(): string
```

**Returns:**

| Period | Label |
|--------|-------|
| DAILY | "Daily" |
| WEEKLY | "Weekly" |
| MONTHLY | "Monthly" |
| QUARTERLY | "Quarterly" |
| YEARLY | "Yearly" |
| LIFETIME | "Lifetime" |

**Example:**

```php
BillingPeriod::MONTHLY->label(); // "Monthly"
```

---

#### addTo()

Add the billing period to a date.

```php
public function addTo(Carbon $date, int $count = 1): Carbon
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$date` | Carbon | Starting date |
| `$count` | int | Number of periods to add |

**Returns:** `Carbon` - New date

**Example:**

```php
$nextBilling = BillingPeriod::MONTHLY->addTo(now(), 1);
// Adds 1 month

$nextBilling = BillingPeriod::YEARLY->addTo(now(), 2);
// Adds 2 years

$lifetime = BillingPeriod::LIFETIME->addTo(now());
// Adds 100 years (effectively never expires)
```

---

#### isLifetime()

Check if this is a lifetime billing period.

```php
public function isLifetime(): bool
```

**Returns:** `bool`

**Example:**

```php
BillingPeriod::LIFETIME->isLifetime(); // true
BillingPeriod::MONTHLY->isLifetime();  // false
```

---

#### days()

Alias for `interval()`.

```php
public function days(): int
```

---

## SubscriptionStatus

`CleaniqueCoders\PackageSubscription\Enums\SubscriptionStatus`

Represents the status of a subscription.

### Values

| Case | Value | Description |
|------|-------|-------------|
| `ACTIVE` | `'active'` | Active subscription |
| `ON_TRIAL` | `'on_trial'` | In trial period |
| `CANCELLED` | `'cancelled'` | Cancelled |
| `SUSPENDED` | `'suspended'` | Temporarily suspended |
| `EXPIRED` | `'expired'` | Has expired |
| `INCOMPLETE` | `'incomplete'` | Incomplete setup |

### Usage

```php
use CleaniqueCoders\PackageSubscription\Enums\SubscriptionStatus;

// Check status
if ($subscription->status === SubscriptionStatus::ACTIVE) {
    // Active subscription
}

// Get string value
$value = SubscriptionStatus::ACTIVE->value; // 'active'

// From database
$status = SubscriptionStatus::from('active'); // SubscriptionStatus::ACTIVE
```

### Methods

#### label()

Get human-readable label.

```php
public function label(): string
```

**Returns:**

| Status | Label |
|--------|-------|
| ACTIVE | "Active" |
| ON_TRIAL | "On Trial" |
| CANCELLED | "Cancelled" |
| SUSPENDED | "Suspended" |
| EXPIRED | "Expired" |
| INCOMPLETE | "Incomplete" |

**Example:**

```php
SubscriptionStatus::ACTIVE->label(); // "Active"

// In blade
{{ $subscription->status->label() }}
```

---

#### isActive()

Check if the status is considered active.

```php
public function isActive(): bool
```

**Returns:** `bool` - True for ACTIVE and ON_TRIAL

**Example:**

```php
SubscriptionStatus::ACTIVE->isActive();    // true
SubscriptionStatus::ON_TRIAL->isActive();  // true
SubscriptionStatus::CANCELLED->isActive(); // false
```

---

#### canAccess()

Check if the status allows feature access.

```php
public function canAccess(): bool
```

**Returns:** `bool` - True for ACTIVE and ON_TRIAL

**Example:**

```php
if ($subscription->status->canAccess()) {
    // Allow access to features
}
```

---

#### badgeClass()

Get CSS class for status badge.

```php
public function badgeClass(): string
```

**Returns:**

| Status | Badge Class |
|--------|-------------|
| ACTIVE | "badge-success" |
| ON_TRIAL | "badge-info" |
| CANCELLED | "badge-warning" |
| SUSPENDED | "badge-secondary" |
| EXPIRED | "badge-danger" |
| INCOMPLETE | "badge-light" |

**Example:**

```blade
<span class="badge {{ $subscription->status->badgeClass() }}">
    {{ $subscription->status->label() }}
</span>
```

---

#### color()

Get color name for the status.

```php
public function color(): string
```

**Returns:**

| Status | Color |
|--------|-------|
| ACTIVE | "green" |
| ON_TRIAL | "blue" |
| CANCELLED | "orange" |
| SUSPENDED | "gray" |
| EXPIRED | "red" |
| INCOMPLETE | "yellow" |

**Example:**

```blade
<span class="text-{{ $subscription->status->color() }}-500">
    {{ $subscription->status->label() }}
</span>
```

---

## Using Enums in Queries

```php
use CleaniqueCoders\PackageSubscription\Enums\SubscriptionStatus;
use CleaniqueCoders\PackageSubscription\Enums\BillingPeriod;

// Find active subscriptions
$active = Subscription::where('status', SubscriptionStatus::ACTIVE)->get();

// Find monthly plans
$monthly = Plan::where('billing_period', BillingPeriod::MONTHLY)->get();

// Find non-active subscriptions
$inactive = Subscription::whereNotIn('status', [
    SubscriptionStatus::ACTIVE,
    SubscriptionStatus::ON_TRIAL,
])->get();
```

## Displaying in Views

```blade
{{-- Status badge --}}
<span class="badge {{ $subscription->status->badgeClass() }}">
    {{ $subscription->status->label() }}
</span>

{{-- Billing period --}}
<p>Billed {{ $plan->billing_period->label() }}</p>

{{-- Conditional display --}}
@if($subscription->status === \CleaniqueCoders\PackageSubscription\Enums\SubscriptionStatus::ON_TRIAL)
    <span class="text-blue-500">
        Trial ends {{ $subscription->trial_ends_at->diffForHumans() }}
    </span>
@endif
```

## JSON Serialization

Enums are automatically serialized to their string values:

```php
$plan = Plan::first();
return response()->json($plan);

// Output includes:
// "billing_period": "monthly"

$subscription = Subscription::first();
return response()->json($subscription);

// Output includes:
// "status": "active"
```
