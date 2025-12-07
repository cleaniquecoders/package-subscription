# Testing

## Running Tests

The package uses [Pest](https://pestphp.com/) for testing.

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage
```

## Testing Your Application

### Testing Subscriptions

When testing subscription features in your application, you can use the package's factories and helpers.

#### Setting Up Test Data

```php
use CleaniqueCoders\PackageSubscription\Models\Plan;
use CleaniqueCoders\PackageSubscription\Models\Subscription;
use App\Models\User;

it('creates a subscription', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create([
        'name' => 'Pro',
        'slug' => 'pro',
        'price' => 29.99,
        'features' => [
            'projects' => 50,
            'api_calls' => 10000,
        ],
    ]);

    $subscription = $user->subscribeTo($plan);

    expect($subscription)->toBeInstanceOf(Subscription::class);
    expect($user->hasActiveSubscription())->toBeTrue();
    expect($user->subscribedTo('pro'))->toBeTrue();
});
```

#### Testing Feature Access

```php
it('checks feature access', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create([
        'features' => [
            'custom_domain' => true,
            'projects' => 10,
        ],
    ]);

    $user->subscribeTo($plan);

    expect($user->canUseFeature('custom_domain'))->toBeTrue();
    expect($user->getFeatureLimit('projects'))->toBe(10);
});
```

#### Testing Usage Tracking

```php
it('tracks usage', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create([
        'features' => ['api_calls' => 100],
    ]);
    $user->subscribeTo($plan);

    $user->recordUsage('api_calls', 50);

    expect($user->getUsage('api_calls'))->toBe(50.0);
    expect($user->exceedsLimit('api_calls'))->toBeFalse();

    $user->recordUsage('api_calls', 60); // Total: 110

    expect($user->exceedsLimit('api_calls'))->toBeTrue();
});
```

#### Testing Subscription Lifecycle

```php
it('handles subscription cancellation', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = $user->subscribeTo($plan);

    $subscription->cancel();

    expect($subscription->fresh()->cancelled_at)->not->toBeNull();
});

it('handles subscription renewal', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['billing_period' => 'monthly']);
    $subscription = $user->subscribeTo($plan);

    $oldEndsAt = $subscription->ends_at;
    $subscription->renew();

    expect($subscription->fresh()->ends_at)->toBeGreaterThan($oldEndsAt);
});
```

### Testing Middleware

```php
use function Pest\Laravel\actingAs;

it('blocks access without subscription', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('home'));
});

it('allows access with subscription', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $user->subscribeTo($plan);

    actingAs($user)
        ->get('/dashboard')
        ->assertOk();
});

it('blocks access without required plan', function () {
    $user = User::factory()->create();
    $basicPlan = Plan::factory()->create(['slug' => 'basic']);
    $user->subscribeTo($basicPlan);

    actingAs($user)
        ->get('/pro-features') // Requires 'pro' plan
        ->assertRedirect(route('home'));
});
```

### Testing Events

```php
use CleaniqueCoders\PackageSubscription\Events\SubscriptionCreated;
use CleaniqueCoders\PackageSubscription\Events\SubscriptionCancelled;
use Illuminate\Support\Facades\Event;

it('fires subscription created event', function () {
    Event::fake([SubscriptionCreated::class]);

    $user = User::factory()->create();
    $plan = Plan::factory()->create();

    $user->subscribeTo($plan);

    Event::assertDispatched(SubscriptionCreated::class);
});

it('fires subscription cancelled event', function () {
    Event::fake([SubscriptionCancelled::class]);

    $user = User::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = $user->subscribeTo($plan);

    $subscription->cancel();

    Event::assertDispatched(SubscriptionCancelled::class);
});
```

### Test Helpers

Create a test helper trait for common subscription setup:

```php
<?php

namespace Tests\Traits;

use CleaniqueCoders\PackageSubscription\Models\Plan;
use App\Models\User;

trait HasSubscriptionTests
{
    protected function createSubscribedUser(array $planAttributes = []): User
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create($planAttributes);
        $user->subscribeTo($plan);

        return $user;
    }

    protected function createPlanWithFeatures(array $features): Plan
    {
        return Plan::factory()->create([
            'features' => $features,
        ]);
    }
}
```

Use in your tests:

```php
uses(Tests\Traits\HasSubscriptionTests::class);

it('does something with subscribed user', function () {
    $user = $this->createSubscribedUser([
        'features' => ['projects' => 100],
    ]);

    expect($user->getFeatureLimit('projects'))->toBe(100);
});
```

## Static Analysis

The package uses PHPStan at Level 5 for static analysis:

```bash
composer analyse
```

## Code Style

Format code using Laravel Pint:

```bash
composer format
```

## Continuous Integration

The package includes GitHub Actions workflows for:

- Running tests on push/PR
- PHPStan static analysis
- Code style checking with Pint

These run automatically on the `main` branch and pull requests.
