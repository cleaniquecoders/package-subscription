# Access Control

Control access to features and routes based on subscription status.

## Middleware

The package provides three middleware for route protection:

### Subscribed Middleware

Require any active subscription:

```php
// routes/web.php
Route::middleware(['auth', 'subscribed'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/projects', [ProjectController::class, 'index']);
});
```

### SubscribedToPlan Middleware

Require a specific plan:

```php
Route::middleware(['auth', 'subscribed.plan:pro'])->group(function () {
    Route::get('/advanced-analytics', [AnalyticsController::class, 'advanced']);
});

// Multiple plans
Route::middleware(['auth', 'subscribed.plan:pro,enterprise'])->group(function () {
    Route::get('/premium-features', [PremiumController::class, 'index']);
});
```

### Feature Middleware

Require access to a specific feature:

```php
Route::middleware(['auth', 'feature:custom_domain'])->group(function () {
    Route::get('/domains', [DomainController::class, 'index']);
    Route::post('/domains', [DomainController::class, 'store']);
});

Route::middleware(['auth', 'feature:api_access'])->group(function () {
    Route::get('/api-keys', [ApiKeyController::class, 'index']);
});
```

## Middleware Redirects

Configure redirect routes in `config/package-subscription.php`:

```php
'redirect' => [
    'no_subscription' => 'pricing',    // No active subscription
    'wrong_plan' => 'upgrade',          // Not on required plan
    'no_feature' => 'upgrade',          // Feature not available
    'expired' => 'renew',               // Subscription expired
],
```

## Blade Directives

### @subscribed

Show content only to subscribers:

```blade
@subscribed
    <div class="dashboard">
        Welcome to your dashboard!
    </div>
@else
    <div class="cta">
        <h2>Subscribe to access the dashboard</h2>
        <a href="{{ route('pricing') }}">View Plans</a>
    </div>
@endsubscribed
```

### @notSubscribed

Show content only to non-subscribers:

```blade
@notSubscribed
    <div class="upgrade-banner">
        <p>Get unlimited access!</p>
        <a href="{{ route('pricing') }}">Subscribe Now</a>
    </div>
@endnotSubscribed
```

### @subscribedToPlan

Show content for specific plans:

```blade
@subscribedToPlan('pro')
    <a href="{{ route('analytics.advanced') }}">
        Advanced Analytics
    </a>
@endsubscribedToPlan

@subscribedToPlan('enterprise')
    <a href="{{ route('admin.settings') }}">
        Admin Settings
    </a>
@endsubscribedToPlan
```

### @notSubscribedToPlan

Show upgrade prompts:

```blade
@notSubscribedToPlan('pro')
    <div class="upgrade-prompt">
        <p>Upgrade to Pro for advanced features!</p>
        <a href="{{ route('upgrade', 'pro') }}">Upgrade Now</a>
    </div>
@endnotSubscribedToPlan
```

### @feature

Show content based on feature access:

```blade
@feature('custom_domain')
    <div class="domain-settings">
        <h3>Custom Domain</h3>
        <a href="{{ route('domains.index') }}">Manage Domains</a>
    </div>
@else
    <div class="feature-locked">
        <p>Custom domains require Pro plan</p>
        <a href="{{ route('upgrade') }}">Upgrade</a>
    </div>
@endfeature
```

### @notFeature

Show upgrade prompts for locked features:

```blade
@notFeature('white_label')
    <div class="locked-feature">
        🔒 White-label branding requires Enterprise plan
    </div>
@endnotFeature
```

## Programmatic Checks

### In Controllers

```php
class DomainController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user->hasActiveSubscription()) {
            return redirect()->route('pricing')
                ->with('message', 'Subscribe to manage domains');
        }

        if (!$user->canUseFeature('custom_domain')) {
            return redirect()->route('upgrade')
                ->with('message', 'Upgrade to Pro for custom domains');
        }

        return view('domains.index', [
            'domains' => $user->domains,
        ]);
    }
}
```

### In Form Requests

```php
class CreateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        // Check subscription
        if (!$user->hasActiveSubscription()) {
            return false;
        }

        // Check project limit
        return $user->withinLimit('projects', $user->projects()->count());
    }

    public function failedAuthorization()
    {
        throw new AuthorizationException(
            'Project limit reached. Upgrade your plan.'
        );
    }
}
```

### In Policies

```php
class ProjectPolicy
{
    public function create(User $user): bool
    {
        if (!$user->hasActiveSubscription()) {
            return false;
        }

        return $user->withinLimit('projects', $user->projects()->count());
    }

    public function useAdvancedFeatures(User $user, Project $project): bool
    {
        return $user->canUseFeature('advanced_project_features');
    }
}
```

### In Models

```php
class User extends Authenticatable
{
    use HasSubscriptions;

    public function canCreateProject(): bool
    {
        return $this->hasActiveSubscription()
            && $this->withinLimit('projects', $this->projects()->count());
    }

    public function canUseApi(): bool
    {
        return $this->canUseFeature('api_access')
            && $this->withinLimit('api_calls');
    }
}
```

## API Authentication

For API routes, return proper HTTP status codes:

```php
class ApiAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user->hasActiveSubscription()) {
            return response()->json([
                'error' => 'Subscription required',
                'code' => 'subscription_required',
            ], 402); // Payment Required
        }

        if (!$user->canUseFeature('api_access')) {
            return response()->json([
                'error' => 'API access not included in your plan',
                'code' => 'feature_not_available',
            ], 403);
        }

        if ($user->exceedsLimit('api_calls')) {
            return response()->json([
                'error' => 'API rate limit exceeded',
                'code' => 'rate_limit_exceeded',
                'limit' => $user->getFeatureLimit('api_calls'),
                'used' => $user->getUsage('api_calls'),
            ], 429);
        }

        return $next($request);
    }
}
```

## Vue/React Integration

Pass subscription data to frontend:

```php
// In a service provider or middleware
Inertia::share([
    'subscription' => fn () => auth()->check() ? [
        'active' => auth()->user()->hasActiveSubscription(),
        'plan' => auth()->user()->activeSubscription()?->plan->slug,
        'onTrial' => auth()->user()->onTrial(),
        'features' => auth()->user()->activeSubscription()?->snapshot ?? [],
    ] : null,
]);
```

Use in Vue component:

```vue
<template>
    <div>
        <div v-if="$page.props.subscription?.active">
            <span v-if="canUseFeature('custom_domain')">
                <router-link to="/domains">Manage Domains</router-link>
            </span>
        </div>
        <div v-else>
            <router-link to="/pricing">Subscribe Now</router-link>
        </div>
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'

const page = usePage()

function canUseFeature(feature) {
    const features = page.props.subscription?.features ?? {}
    return features[feature] === true || features[feature] > 0
}
</script>
```

## Custom Middleware

Create custom middleware for specific needs:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireProOrEnterprise
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user?->hasActiveSubscription()) {
            return redirect()->route('pricing');
        }

        $allowedPlans = ['pro', 'enterprise'];
        $currentPlan = $user->activeSubscription()->plan->slug;

        if (!in_array($currentPlan, $allowedPlans)) {
            return redirect()->route('upgrade')
                ->with('error', 'This feature requires Pro or Enterprise plan');
        }

        return $next($request);
    }
}
```

Register in `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    // ...
    'pro-or-enterprise' => \App\Http\Middleware\RequireProOrEnterprise::class,
];
```
