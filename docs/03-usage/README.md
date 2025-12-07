# Usage

This section covers practical usage of the Package Subscription features in your Laravel application.

## Table of Contents

1. [Plan Management](./01-plan-management.md) - Creating and managing subscription plans
2. [Subscriptions](./02-subscriptions.md) - Subscription lifecycle management
3. [Usage Tracking](./03-usage-tracking.md) - Track and enforce feature usage limits
4. [Access Control](./04-access-control.md) - Middleware and Blade directives

## Quick Reference

### Common Operations

```php
// Subscribe user to plan
$user->subscribeTo($plan);

// Check subscription status
$user->hasActiveSubscription();
$user->subscribedTo('pro');

// Check feature access
$user->canUseFeature('custom_domain');
$user->getFeatureLimit('projects');

// Track usage
$user->recordUsage('api_calls', 100);
$user->getUsage('api_calls');

// Manage subscription
$subscription = $user->activeSubscription();
$subscription->cancel();
$subscription->renew();
$subscription->switchTo($newPlan);
```

### Middleware Quick Reference

```php
// Require active subscription
Route::middleware('subscribed')->group(...);

// Require specific plan
Route::middleware('subscribed.plan:pro')->group(...);

// Require feature access
Route::middleware('feature:custom_domain')->group(...);
```

### Blade Directives Quick Reference

```blade
@subscribed ... @endsubscribed
@subscribedToPlan('pro') ... @endsubscribedToPlan
@feature('custom_domain') ... @endfeature
```

## Next Steps

- Learn about [plan management](./01-plan-management.md)
- Understand [subscription lifecycle](./02-subscriptions.md)
- Implement [usage tracking](./03-usage-tracking.md)
- Set up [access control](./04-access-control.md)
