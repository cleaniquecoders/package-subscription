# Usage Tracking

Track and enforce feature usage limits for subscriptions.

## Recording Usage

### Record Specific Amount

```php
// Set usage to specific amount
$user->recordUsage('api_calls', 100);
```

### Increment Usage

```php
// Add to current usage
$user->incrementUsage('api_calls', 1);
$user->incrementUsage('storage', 0.5); // Supports decimals
```

### Decrement Usage

```php
// Subtract from current usage
$user->decrementUsage('storage', 2);
```

## Querying Usage

### Get Current Usage

```php
$usage = $user->getUsage('api_calls'); // 150
```

### Get Usage Percentage

```php
$percentage = $user->getUsagePercentage('api_calls'); // 15.0 (15% of 1000 limit)
```

### Get Remaining

```php
$remaining = $user->getRemainingUsage('api_calls'); // 850
```

## Checking Limits

### Check if Within Limit

```php
if ($user->withinLimit('projects', $currentProjectCount)) {
    // Can create more projects
}

// Or check against recorded usage
if ($user->withinLimit('api_calls')) {
    // Haven't exceeded API call limit
}
```

### Check if Exceeds Limit

```php
if ($user->exceedsLimit('api_calls')) {
    // Show upgrade prompt
    return response()->json([
        'error' => 'API call limit exceeded',
        'limit' => $user->getFeatureLimit('api_calls'),
        'used' => $user->getUsage('api_calls'),
    ], 429);
}
```

## Resetting Usage

### Reset All Usage

```php
$subscription = $user->activeSubscription();
$subscription->resetUsage();
```

### Reset Specific Feature

```php
$subscription->resetUsage('api_calls');
```

### Automatic Reset on Renewal

Usage is automatically reset when configured:

```php
// config/package-subscription.php
'usage' => [
    'reset_on_renewal' => true,
],
```

## Usage Service

For more control, use the `UsageService` directly:

```php
use CleaniqueCoders\PackageSubscription\Services\UsageService;

$usageService = app(UsageService::class);
$subscription = $user->activeSubscription();

// Record usage
$usage = $usageService->record($subscription, 'api_calls', 100);

// Increment
$usage = $usageService->increment($subscription, 'api_calls', 5);

// Decrement
$usage = $usageService->decrement($subscription, 'api_calls', 2);

// Get usage
$used = $usageService->get($subscription, 'api_calls');

// Get remaining
$remaining = $usageService->getRemaining($subscription, 'api_calls');

// Reset
$usageService->reset($subscription, 'api_calls');
$usageService->reset($subscription); // Reset all
```

## Usage Model

### Accessing Usage Records

```php
$subscription = $user->activeSubscription();

// Get all usage records
$usages = $subscription->usages;

// Get specific usage
$apiUsage = $subscription->usages()
    ->where('feature', 'api_calls')
    ->first();
```

### Usage Attributes

```php
$usage = $subscription->usages()->where('feature', 'api_calls')->first();

$usage->feature;      // 'api_calls'
$usage->used;         // 150.0
$usage->limit;        // 1000
$usage->reset_at;     // Carbon (last reset time)
```

### Usage Methods

```php
$usage->isWithinLimit();  // true if used <= limit
$usage->isExceeded();     // true if used > limit
$usage->remaining();      // limit - used
$usage->percentage();     // (used / limit) * 100
```

## Practical Examples

### API Rate Limiting

```php
// In a middleware or controller
public function handle(Request $request, Closure $next)
{
    $user = $request->user();

    if ($user->exceedsLimit('api_calls')) {
        return response()->json([
            'error' => 'Rate limit exceeded',
            'limit' => $user->getFeatureLimit('api_calls'),
            'used' => $user->getUsage('api_calls'),
            'reset_at' => $user->activeSubscription()
                ->usages()
                ->where('feature', 'api_calls')
                ->first()
                ->reset_at
                ->toIso8601String(),
        ], 429);
    }

    // Record this API call
    $user->incrementUsage('api_calls');

    return $next($request);
}
```

### Storage Tracking

```php
// When uploading a file
public function store(Request $request)
{
    $user = $request->user();
    $file = $request->file('document');
    $sizeInGB = $file->getSize() / (1024 * 1024 * 1024);

    // Check if within storage limit
    $currentUsage = $user->getUsage('storage');
    $limit = $user->getFeatureLimit('storage');

    if (($currentUsage + $sizeInGB) > $limit) {
        return back()->withError('Storage limit exceeded');
    }

    // Store file and record usage
    $path = $file->store('documents');
    $user->incrementUsage('storage', $sizeInGB);

    return back()->withSuccess('File uploaded');
}

// When deleting a file
public function destroy(Document $document)
{
    $sizeInGB = $document->size / (1024 * 1024 * 1024);

    Storage::delete($document->path);
    $document->delete();

    // Decrement usage
    $document->user->decrementUsage('storage', $sizeInGB);
}
```

### Resource Count Tracking

```php
// When creating a project
public function store(Request $request)
{
    $user = $request->user();

    if ($user->exceedsLimit('projects')) {
        return back()->withError(
            'Project limit reached. Upgrade to create more projects.'
        );
    }

    $project = Project::create([
        'user_id' => $user->id,
        'name' => $request->name,
    ]);

    $user->incrementUsage('projects');

    return redirect()->route('projects.show', $project);
}

// When deleting a project
public function destroy(Project $project)
{
    $user = $project->user;

    $project->delete();
    $user->decrementUsage('projects');

    return redirect()->route('projects.index');
}
```

## Usage Events

Events are dispatched when usage is recorded:

```php
use CleaniqueCoders\PackageSubscription\Events\UsageRecorded;
use CleaniqueCoders\PackageSubscription\Events\UsageLimitExceeded;

// In EventServiceProvider
protected $listen = [
    UsageRecorded::class => [
        LogUsageActivity::class,
    ],
    UsageLimitExceeded::class => [
        SendUpgradeReminder::class,
        NotifyAccountManager::class,
    ],
];
```

### Event Listener Example

```php
use CleaniqueCoders\PackageSubscription\Events\UsageLimitExceeded;

class SendUpgradeReminder
{
    public function handle(UsageLimitExceeded $event): void
    {
        $usage = $event->usage;
        $subscription = $usage->subscription;
        $user = $subscription->subscribable;

        $user->notify(new UpgradeReminderNotification(
            feature: $usage->feature,
            used: $usage->used,
            limit: $usage->limit,
        ));
    }
}
```

## Displaying Usage

### Progress Bar Example

```blade
@php
    $subscription = auth()->user()->activeSubscription();
    $features = ['projects', 'storage', 'api_calls'];
@endphp

<div class="usage-dashboard">
    @foreach ($features as $feature)
        @php
            $usage = $subscription->usages()->where('feature', $feature)->first();
            $percentage = $usage?->percentage() ?? 0;
            $used = $usage?->used ?? 0;
            $limit = auth()->user()->getFeatureLimit($feature);
        @endphp

        <div class="usage-item">
            <div class="usage-header">
                <span>{{ ucfirst($feature) }}</span>
                <span>{{ $used }} / {{ $limit }}</span>
            </div>
            <div class="progress-bar">
                <div class="progress" style="width: {{ min($percentage, 100) }}%"></div>
            </div>
        </div>
    @endforeach
</div>
```

## Artisan Command

Reset usage for all subscriptions:

```bash
# Reset all usage
php artisan subscription:reset-usage

# Reset specific feature
php artisan subscription:reset-usage --feature=api_calls
```
