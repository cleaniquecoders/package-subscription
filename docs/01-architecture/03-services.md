# Services

The package uses a service layer to encapsulate business logic for subscriptions, usage tracking, and proration calculations.

## SubscriptionService

The `SubscriptionService` handles subscription lifecycle operations.

### Creating Subscriptions

```php
use CleaniqueCoders\PackageSubscription\Services\SubscriptionService;

$service = app(SubscriptionService::class);

// Create a basic subscription
$subscription = $service->create($user, $plan);

// Create with options
$subscription = $service->create($user, $plan, [
    'starts_at' => now(),
    'with_trial' => true,
    'trial_days' => 14,
    'metadata' => ['source' => 'web'],
]);
```

### Managing Subscriptions

```php
// Renew
$subscription = $service->renew($subscription);

// Cancel
$subscription = $service->cancel($subscription);           // At period end
$subscription = $service->cancel($subscription, true);     // Immediately

// Suspend
$subscription = $service->suspend($subscription, 'Payment failed');

// Resume
$subscription = $service->resume($subscription);

// Expire
$subscription = $service->expire($subscription);

// Change plan
$subscription = $service->changePlan($subscription, $newPlan, [
    'prorate' => true,
]);
```

## UsageService

The `UsageService` handles feature usage tracking.

### Recording Usage

```php
use CleaniqueCoders\PackageSubscription\Services\UsageService;

$service = app(UsageService::class);

// Record specific amount
$usage = $service->record($subscription, 'api_calls', 100);

// Increment by amount
$usage = $service->increment($subscription, 'api_calls', 5);

// Decrement by amount
$usage = $service->decrement($subscription, 'api_calls', 2);

// Set to specific value (alias of record)
$usage = $service->set($subscription, 'storage', 25.5);
```

### Querying Usage

```php
// Get current usage
$used = $service->get($subscription, 'api_calls'); // 105

// Get remaining
$remaining = $service->getRemaining($subscription, 'api_calls'); // 895

// Get percentage used
$percentage = $service->getPercentage($subscription, 'storage'); // 51.0
```

### Resetting Usage

```php
// Reset all usage for subscription
$service->reset($subscription);

// Reset specific feature
$service->reset($subscription, 'api_calls');
```

## ProrationService

The `ProrationService` calculates proration amounts for mid-cycle plan changes.

### Proration Calculation

```php
use CleaniqueCoders\PackageSubscription\Services\ProrationService;

$service = app(ProrationService::class);

// Calculate proration credit from current plan
$credit = $service->calculateCredit($subscription);

// Calculate amount due for new plan
$charge = $service->calculateCharge($subscription, $newPlan);

// Get net proration amount
$proration = $service->calculate($subscription, $newPlan);
// Returns: ['credit' => 15.00, 'charge' => 25.00, 'net' => 10.00]
```

### Proration Modes

The proration mode is configurable in `config/package-subscription.php`:

```php
'proration' => [
    'enabled' => true,
    'rounding' => 2,        // Decimal places
    'mode' => 'daily',      // 'daily' or 'hourly'
],
```

- **daily** - Calculates based on remaining days in period
- **hourly** - More precise calculation based on remaining hours

## Service Resolution

Services are registered in the container and can be resolved via dependency injection:

```php
use CleaniqueCoders\PackageSubscription\Services\SubscriptionService;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    public function store(Request $request, Plan $plan)
    {
        $subscription = $this->subscriptionService->create(
            $request->user(),
            $plan,
            $request->validated()
        );

        return response()->json($subscription);
    }
}
```

Or via the `app()` helper:

```php
$service = app(SubscriptionService::class);
```

## Event Dispatching

Services automatically dispatch events during operations:

| Operation | Event |
|-----------|-------|
| `create()` | `SubscriptionCreated` |
| `renew()` | `SubscriptionRenewed` |
| `cancel()` | `SubscriptionCancelled` |
| `suspend()` | `SubscriptionSuspended` |
| `resume()` | `SubscriptionResumed` |
| `expire()` | `SubscriptionExpired` |
| `changePlan()` | `PlanChanged` |
| `record()` / `increment()` | `UsageRecorded` |
| (when limit exceeded) | `UsageLimitExceeded` |

Event dispatching can be configured:

```php
// config/package-subscription.php
'events' => [
    'dispatch' => true,   // Enable/disable events
    'queue' => false,     // Queue event handlers
],
```
