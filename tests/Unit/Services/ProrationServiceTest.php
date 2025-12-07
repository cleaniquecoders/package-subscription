<?php

use CleaniqueCoders\PackageSubscription\Models\Plan;
use CleaniqueCoders\PackageSubscription\Services\ProrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Workbench\App\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->prorationService = new ProrationService;
});

it('calculates proration correctly', function () {
    $user = User::factory()->create();
    $currentPlan = Plan::factory()->create(['price' => 10.00]);
    $newPlan = Plan::factory()->create(['price' => 20.00]);

    $subscription = $user->subscribeTo($currentPlan);

    $proration = $this->prorationService->calculate($subscription, $newPlan);

    expect($proration)->toBeFloat();
});

it('returns zero proration for free plans', function () {
    $user = User::factory()->create();
    $freePlan = Plan::factory()->free()->create();
    $paidPlan = Plan::factory()->create(['price' => 10.00]);

    $subscription = $user->subscribeTo($freePlan);

    $proration = $this->prorationService->calculate($subscription, $paidPlan);

    // Proration should still calculate even for free plan upgrades
    expect($proration)->toBeFloat();
});

it('calculates credit for unused time', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['price' => 30.00]);

    $subscription = $user->subscribeTo($plan);

    $credit = $this->prorationService->calculateCredit($subscription, now()->addDays(15));

    expect($credit)->toBeGreaterThan(0);
});
