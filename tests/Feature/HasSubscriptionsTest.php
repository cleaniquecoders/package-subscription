<?php

use CleaniqueCoders\PackageSubscription\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Workbench\App\Models\User;

uses(RefreshDatabase::class);

it('can subscribe to a plan', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();

    $subscription = $user->subscribeTo($plan);

    expect($subscription)->not->toBeNull()
        ->and($subscription->plan_id)->toBe($plan->id)
        ->and($subscription->status->value)->toBe('active');
});

it('can check if user has active subscription', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();

    expect($user->hasActiveSubscription())->toBeFalse();

    $user->subscribeTo($plan);

    expect($user->hasActiveSubscription())->toBeTrue();
});

it('can check if subscribed to specific plan', function () {
    $user = User::factory()->create();
    $basicPlan = Plan::factory()->create(['slug' => 'basic']);
    $proPlan = Plan::factory()->create(['slug' => 'pro']);

    $user->subscribeTo($basicPlan);

    expect($user->subscribedTo('basic'))->toBeTrue()
        ->and($user->subscribedTo('pro'))->toBeFalse()
        ->and($user->subscribedTo($basicPlan))->toBeTrue()
        ->and($user->subscribedTo($proPlan))->toBeFalse();
});

it('can check feature access', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create([
        'features' => [
            'api_calls' => 1000,
            'custom_domain' => true,
        ],
    ]);

    $user->subscribeTo($plan);

    expect($user->canUseFeature('api_calls'))->toBeTrue()
        ->and($user->canUseFeature('custom_domain'))->toBeTrue()
        ->and($user->canUseFeature('advanced_analytics'))->toBeFalse();
});

it('can record usage', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create([
        'features' => [
            'api_calls' => 1000,
        ],
    ]);

    $user->subscribeTo($plan);
    $user->recordUsage('api_calls', 50);

    expect($user->getUsage('api_calls'))->toBe(50.0);
});

it('can increment usage', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create([
        'features' => [
            'api_calls' => 1000,
        ],
    ]);

    $user->subscribeTo($plan);
    $user->incrementUsage('api_calls', 10);
    $user->incrementUsage('api_calls', 5);

    expect($user->getUsage('api_calls'))->toBe(15.0);
});

it('can check if usage exceeds limit', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create([
        'features' => [
            'api_calls' => 100,
        ],
    ]);

    $user->subscribeTo($plan);

    expect($user->exceedsLimit('api_calls'))->toBeFalse();

    $user->recordUsage('api_calls', 150);

    expect($user->exceedsLimit('api_calls'))->toBeTrue();
});

it('can cancel subscription', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();

    $user->subscribeTo($plan);
    $user->cancelSubscription();

    $subscription = $user->activeSubscription();

    expect($subscription->cancelled_at)->not->toBeNull();
});
