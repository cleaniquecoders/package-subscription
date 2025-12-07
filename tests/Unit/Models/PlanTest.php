<?php

use CleaniqueCoders\PackageSubscription\Models\Plan;

it('can create a plan', function () {
    $plan = Plan::factory()->create([
        'name' => 'Basic Plan',
        'price' => 9.99,
    ]);

    expect($plan->name)->toBe('Basic Plan')
        ->and($plan->price)->toBe('9.99');
});

it('can check if plan has a feature', function () {
    $plan = Plan::factory()->create([
        'features' => [
            'api_calls' => 1000,
            'storage' => 5,
        ],
    ]);

    expect($plan->hasFeature('api_calls'))->toBeTrue()
        ->and($plan->hasFeature('custom_domain'))->toBeFalse();
});

it('can get feature value', function () {
    $plan = Plan::factory()->create([
        'features' => [
            'api_calls' => 1000,
            'custom_domain' => true,
        ],
    ]);

    expect($plan->getFeatureValue('api_calls'))->toBe(1000)
        ->and($plan->getFeatureValue('custom_domain'))->toBeTrue();
});

it('can get feature limit', function () {
    $plan = Plan::factory()->create([
        'features' => [
            'api_calls' => 1000,
        ],
    ]);

    expect($plan->getFeatureLimit('api_calls'))->toBe(1000);
});

it('can check if plan is free', function () {
    $freePlan = Plan::factory()->free()->create();
    $paidPlan = Plan::factory()->create(['price' => 9.99]);

    expect($freePlan->isFree())->toBeTrue()
        ->and($paidPlan->isFree())->toBeFalse();
});

it('can calculate next billing date', function () {
    $plan = Plan::factory()->monthly()->create();

    $nextBillingDate = $plan->calculateNextBillingDate(now());

    expect($nextBillingDate)->toBeInstanceOf(\Carbon\Carbon::class)
        ->and($nextBillingDate->isAfter(now()))->toBeTrue();
});
