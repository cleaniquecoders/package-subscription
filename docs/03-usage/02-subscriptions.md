# Subscriptions

## Subscribing to a Plan

### Basic Subscription

```php
use CleaniqueCoders\PackageSubscription\Models\Plan;

$user = auth()->user();
$plan = Plan::where('slug', 'pro')->first();

// Subscribe the user
$subscription = $user->subscribeTo($plan);
```

### Subscribe by Plan Slug

```php
// Pass plan slug instead of model
$subscription = $user->subscribeTo('pro');
```

### Subscribe with Options

```php
$subscription = $user->subscribeTo($plan, [
    'starts_at' => now(),
    'with_trial' => true,
    'trial_days' => 7,        // Override plan's trial period
    'metadata' => [
        'source' => 'homepage',
        'campaign' => 'summer-sale',
    ],
]);
```

### Skip Trial Period

```php
$subscription = $user->subscribeTo($plan, [
    'with_trial' => false,
]);
```

## Checking Subscription Status

### Has Active Subscription

```php
if ($user->hasActiveSubscription()) {
    // User has an active subscription
}

// Aliases
$user->hasSubscription();
$user->isSubscribed();
```

### Check Specific Plan

```php
if ($user->subscribedTo('pro')) {
    // User is on the Pro plan
}

// Aliases
$user->isSubscribedTo('pro');
$user->subscribedToPlan('pro');

// Pass plan instance
$plan = Plan::where('slug', 'pro')->first();
if ($user->subscribedTo($plan)) {
    // ...
}
```

### Check Trial Status

```php
if ($user->onTrial()) {
    // User is in trial period
}
```

### Check Grace Period

```php
if ($user->onGracePeriod()) {
    // User is in grace period
}
```

## Getting Subscription Details

### Get Active Subscription

```php
$subscription = $user->activeSubscription();

// Alias
$subscription = $user->subscription();

// Access subscription properties
$subscription->plan;          // Plan model
$subscription->status;        // SubscriptionStatus enum
$subscription->starts_at;     // Carbon
$subscription->ends_at;       // Carbon
$subscription->trial_ends_at; // Carbon (nullable)
$subscription->price;         // Decimal
```

### Get Subscription History

```php
$history = $user->subscriptionHistory();

foreach ($history as $subscription) {
    echo $subscription->plan->name;
    echo $subscription->status->label();
    echo $subscription->created_at;
}
```

## Subscription Lifecycle

### Cancel Subscription

```php
$subscription = $user->activeSubscription();

// Cancel at end of billing period
$subscription->cancel();

// Cancel immediately
$subscription->cancel(immediately: true);

// Alias for cancel at period end
$subscription->cancelAtPeriodEnd();
```

### Resume Cancelled Subscription

```php
// If cancelled but period hasn't ended yet
$subscription->resume();
```

### Suspend Subscription

```php
// Suspend with optional reason
$subscription->suspend('Payment failed');

// Resume suspended subscription
$subscription->resume();
```

### Renew Subscription

```php
// Manually renew subscription
$subscription->renew();
```

### Expire Subscription

```php
// Manually expire subscription
$subscription->expire();
```

## Checking Subscription State

```php
$subscription = $user->activeSubscription();

$subscription->isActive();       // Active or on trial
$subscription->isCancelled();    // Has been cancelled
$subscription->isSuspended();    // Currently suspended
$subscription->isExpired();      // Has expired
$subscription->isOnTrial();      // In trial period
$subscription->isOnGracePeriod(); // In grace period
$subscription->hasEnded();       // Billing period ended
```

## Plan Changes

### Upgrade to Higher Plan

```php
$newPlan = Plan::where('slug', 'enterprise')->first();

$subscription->upgradeTo($newPlan);
```

### Downgrade to Lower Plan

```php
$newPlan = Plan::where('slug', 'basic')->first();

$subscription->downgradeTo($newPlan);
```

### Switch Plans with Options

```php
$subscription->switchTo($newPlan, [
    'prorate' => true,
    'invoice_now' => false,
]);
```

### Get Proration Amount

```php
use CleaniqueCoders\PackageSubscription\Services\ProrationService;

$prorationService = app(ProrationService::class);
$proration = $prorationService->calculate($subscription, $newPlan);

// Returns:
// [
//     'credit' => 15.00,  // Credit from remaining current period
//     'charge' => 25.00,  // Charge for new plan
//     'net' => 10.00,     // Net amount due
// ]
```

## Feature Access

### Check Feature Access

```php
// Check if user can use a feature
if ($user->canUseFeature('custom_domain')) {
    // Feature is available
}

// Alias
$user->hasFeature('custom_domain');
```

### Get Feature Value

```php
// Get feature value (any type)
$supportType = $user->getFeatureValue('support'); // 'priority'

// Get numeric limit
$projectLimit = $user->getFeatureLimit('projects'); // 50
```

### Check Limits

```php
// Check if within limit
if ($user->withinLimit('projects', $currentProjectCount)) {
    // User hasn't exceeded project limit
}

// Check if exceeds limit
if ($user->exceedsLimit('api_calls')) {
    // User has exceeded API call limit
}
```

## Working with Subscription Model

### Direct Subscription Queries

```php
use CleaniqueCoders\PackageSubscription\Models\Subscription;

// Get all active subscriptions
$active = Subscription::active()->get();

// Get subscriptions on trial
$trials = Subscription::onTrial()->get();

// Get subscriptions for a plan
$proSubscriptions = Subscription::forPlan('pro')->get();

// Get cancelled subscriptions
$cancelled = Subscription::cancelled()->get();

// Get expired subscriptions
$expired = Subscription::expired()->get();
```

### Subscription Relationships

```php
$subscription = $user->activeSubscription();

// Get the plan
$plan = $subscription->plan;

// Get the subscriber (User, Team, etc.)
$subscriber = $subscription->subscribable;

// Get usage records
$usages = $subscription->usages;

// Get history records
$history = $subscription->history;
```

## Subscription Snapshot

When a subscription is created, the plan features are "snapshotted" to preserve them even if the plan changes:

```php
$subscription = $user->activeSubscription();

// Get snapshotted features (frozen at subscription time)
$features = $subscription->snapshot;

// These won't change even if plan features are updated
$subscription->snapshot['projects']; // 50
```

This ensures users keep the features they signed up for.

## Team/Organization Subscriptions

The trait works with any Eloquent model:

```php
// Team-based subscription
$team = Team::find(1);
$team->subscribeTo($plan);

// Check team subscription
if ($team->hasActiveSubscription()) {
    // Team is subscribed
}

// User checks via team
if ($user->team->subscribedTo('enterprise')) {
    // User's team has enterprise plan
}
```
