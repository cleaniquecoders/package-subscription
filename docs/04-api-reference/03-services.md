# Services

## SubscriptionService

`CleaniqueCoders\PackageSubscription\Services\SubscriptionService`

Handles subscription lifecycle operations.

### Resolution

```php
use CleaniqueCoders\PackageSubscription\Services\SubscriptionService;

// Via dependency injection
public function __construct(private SubscriptionService $subscriptionService) {}

// Via app helper
$service = app(SubscriptionService::class);
```

---

### create()

Create a new subscription.

```php
public function create(Model $subscribable, Plan $plan, array $options = []): Subscription
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$subscribable` | Model | The model to subscribe (User, Team, etc.) |
| `$plan` | Plan | The plan to subscribe to |
| `$options` | array | Subscription options |

**Options:**

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `starts_at` | Carbon | now() | When subscription starts |
| `with_trial` | bool | true | Include trial period |
| `trial_days` | int | Plan default | Override trial days |
| `metadata` | array | [] | Custom metadata |

**Returns:** `Subscription`

**Events:** `SubscriptionCreated`

**Example:**

```php
$subscription = $service->create($user, $plan, [
    'with_trial' => true,
    'trial_days' => 7,
    'metadata' => ['source' => 'api'],
]);
```

---

### renew()

Renew a subscription.

```php
public function renew(Subscription $subscription): Subscription
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$subscription` | Subscription | The subscription to renew |

**Returns:** `Subscription`

**Events:** `SubscriptionRenewed`

**Example:**

```php
$subscription = $service->renew($subscription);
```

---

### cancel()

Cancel a subscription.

```php
public function cancel(Subscription $subscription, bool $immediately = false): Subscription
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$subscription` | Subscription | The subscription to cancel |
| `$immediately` | bool | Cancel immediately or at period end |

**Returns:** `Subscription`

**Events:** `SubscriptionCancelled`

**Example:**

```php
// Cancel at period end
$subscription = $service->cancel($subscription);

// Cancel immediately
$subscription = $service->cancel($subscription, true);
```

---

### suspend()

Suspend a subscription.

```php
public function suspend(Subscription $subscription, ?string $reason = null): Subscription
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$subscription` | Subscription | The subscription to suspend |
| `$reason` | string\|null | Reason for suspension |

**Returns:** `Subscription`

**Events:** `SubscriptionSuspended`

**Example:**

```php
$subscription = $service->suspend($subscription, 'Payment failed');
```

---

### resume()

Resume a suspended or cancelled subscription.

```php
public function resume(Subscription $subscription): Subscription
```

**Returns:** `Subscription`

**Events:** `SubscriptionResumed`

**Example:**

```php
$subscription = $service->resume($subscription);
```

---

### expire()

Expire a subscription.

```php
public function expire(Subscription $subscription): Subscription
```

**Returns:** `Subscription`

**Events:** `SubscriptionExpired`

**Example:**

```php
$subscription = $service->expire($subscription);
```

---

### changePlan()

Change the plan for a subscription.

```php
public function changePlan(Subscription $subscription, Plan $newPlan, array $options = []): Subscription
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$subscription` | Subscription | The subscription to modify |
| `$newPlan` | Plan | The new plan |
| `$options` | array | Change options |

**Options:**

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `prorate` | bool | config value | Calculate proration |
| `invoice_now` | bool | false | Invoice immediately |

**Returns:** `Subscription`

**Events:** `PlanChanged`

**Example:**

```php
$subscription = $service->changePlan($subscription, $newPlan, [
    'prorate' => true,
]);
```

---

## UsageService

`CleaniqueCoders\PackageSubscription\Services\UsageService`

Handles feature usage tracking.

### Resolution

```php
use CleaniqueCoders\PackageSubscription\Services\UsageService;

$service = app(UsageService::class);
```

---

### record()

Record usage for a feature.

```php
public function record(Subscription $subscription, string $feature, float $amount): Usage
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$subscription` | Subscription | The subscription |
| `$feature` | string | Feature identifier |
| `$amount` | float | Amount to record |

**Returns:** `Usage`

**Events:** `UsageRecorded`, `UsageLimitExceeded` (if limit exceeded)

**Example:**

```php
$usage = $service->record($subscription, 'api_calls', 100);
```

---

### increment()

Increment usage for a feature.

```php
public function increment(Subscription $subscription, string $feature, float $amount = 1): Usage
```

**Returns:** `Usage`

**Events:** `UsageRecorded`, `UsageLimitExceeded` (if limit exceeded)

**Example:**

```php
$usage = $service->increment($subscription, 'api_calls', 5);
```

---

### decrement()

Decrement usage for a feature.

```php
public function decrement(Subscription $subscription, string $feature, float $amount = 1): Usage
```

**Returns:** `Usage`

**Example:**

```php
$usage = $service->decrement($subscription, 'storage', 2);
```

---

### set()

Set usage to a specific amount. Alias for `record()`.

```php
public function set(Subscription $subscription, string $feature, float $amount): Usage
```

---

### reset()

Reset usage for a subscription.

```php
public function reset(Subscription $subscription, ?string $feature = null): void
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$subscription` | Subscription | The subscription |
| `$feature` | string\|null | Feature to reset, or null for all |

**Example:**

```php
// Reset all usage
$service->reset($subscription);

// Reset specific feature
$service->reset($subscription, 'api_calls');
```

---

### get()

Get current usage for a feature.

```php
public function get(Subscription $subscription, string $feature): float
```

**Returns:** `float`

**Example:**

```php
$used = $service->get($subscription, 'api_calls'); // 150
```

---

### getRemaining()

Get remaining usage for a feature.

```php
public function getRemaining(Subscription $subscription, string $feature): ?float
```

**Returns:** `float|null` - Returns null if no limit set

**Example:**

```php
$remaining = $service->getRemaining($subscription, 'api_calls'); // 850
```

---

### getPercentage()

Get usage as a percentage of limit.

```php
public function getPercentage(Subscription $subscription, string $feature): float
```

**Returns:** `float` - Percentage (0-100+)

**Example:**

```php
$percentage = $service->getPercentage($subscription, 'storage'); // 51.0
```

---

## ProrationService

`CleaniqueCoders\PackageSubscription\Services\ProrationService`

Calculates proration amounts for plan changes.

### Resolution

```php
use CleaniqueCoders\PackageSubscription\Services\ProrationService;

$service = app(ProrationService::class);
```

---

### calculate()

Calculate full proration for a plan change.

```php
public function calculate(Subscription $subscription, Plan $newPlan): array
```

**Parameters:**

| Name | Type | Description |
|------|------|-------------|
| `$subscription` | Subscription | Current subscription |
| `$newPlan` | Plan | New plan to switch to |

**Returns:** `array`

```php
[
    'credit' => 15.00,   // Credit from remaining current period
    'charge' => 25.00,   // Charge for new plan
    'net' => 10.00,      // Net amount due (charge - credit)
]
```

**Example:**

```php
$proration = $service->calculate($subscription, $newPlan);

if ($proration['net'] > 0) {
    // Charge the customer
    $this->chargeCustomer($user, $proration['net']);
} else {
    // Issue credit
    $this->creditCustomer($user, abs($proration['net']));
}
```

---

### calculateCredit()

Calculate credit for remaining period.

```php
public function calculateCredit(Subscription $subscription): float
```

**Returns:** `float` - Credit amount

---

### calculateCharge()

Calculate charge for new plan.

```php
public function calculateCharge(Subscription $subscription, Plan $newPlan): float
```

**Returns:** `float` - Charge amount

---

## Complete Example

```php
use CleaniqueCoders\PackageSubscription\Services\SubscriptionService;
use CleaniqueCoders\PackageSubscription\Services\UsageService;
use CleaniqueCoders\PackageSubscription\Services\ProrationService;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private UsageService $usageService,
        private ProrationService $prorationService,
    ) {}

    public function subscribe(Request $request, Plan $plan)
    {
        $subscription = $this->subscriptionService->create(
            $request->user(),
            $plan,
            ['metadata' => ['source' => 'checkout']]
        );

        return response()->json($subscription);
    }

    public function upgrade(Request $request, Plan $newPlan)
    {
        $subscription = $request->user()->activeSubscription();

        // Calculate proration
        $proration = $this->prorationService->calculate($subscription, $newPlan);

        // Change plan
        $subscription = $this->subscriptionService->changePlan(
            $subscription,
            $newPlan,
            ['prorate' => true]
        );

        return response()->json([
            'subscription' => $subscription,
            'proration' => $proration,
        ]);
    }

    public function recordApiCall(Request $request)
    {
        $subscription = $request->user()->activeSubscription();

        $this->usageService->increment($subscription, 'api_calls');

        return response()->json([
            'used' => $this->usageService->get($subscription, 'api_calls'),
            'remaining' => $this->usageService->getRemaining($subscription, 'api_calls'),
        ]);
    }
}
```
