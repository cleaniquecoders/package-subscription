<?php

// config for CleaniqueCoders/PackageSubscription
return [

    /*
    |--------------------------------------------------------------------------
    | Subscription Models
    |--------------------------------------------------------------------------
    |
    | Configure which models to use for subscriptions. You can extend these
    | models to add custom functionality.
    |
    */

    'models' => [
        'plan' => \CleaniqueCoders\PackageSubscription\Models\Plan::class,
        'subscription' => \CleaniqueCoders\PackageSubscription\Models\Subscription::class,
        'usage' => \CleaniqueCoders\PackageSubscription\Models\Usage::class,
        'history' => \CleaniqueCoders\PackageSubscription\Models\SubscriptionHistory::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    |
    | Configure table names for subscription-related tables.
    |
    */

    'tables' => [
        'plans' => 'plans',
        'subscriptions' => 'subscriptions',
        'usages' => 'usages',
        'subscription_history' => 'subscription_history',
    ],

    /*
    |--------------------------------------------------------------------------
    | Proration Settings
    |--------------------------------------------------------------------------
    |
    | Configure how proration should be calculated when changing plans.
    |
    */

    'proration' => [
        'enabled' => true,
        'rounding' => 2, // Decimal places for rounding
        'mode' => 'daily', // 'daily' or 'hourly'
    ],

    /*
    |--------------------------------------------------------------------------
    | Trial Period
    |--------------------------------------------------------------------------
    |
    | Default settings for trial periods.
    |
    */

    'trial' => [
        'enabled' => true,
        'default_days' => 14,
        'require_payment_method' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Grace Period
    |--------------------------------------------------------------------------
    |
    | Default settings for grace periods after subscription ends.
    |
    */

    'grace_period' => [
        'enabled' => true,
        'default_days' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Usage Tracking
    |--------------------------------------------------------------------------
    |
    | Configure usage tracking behavior.
    |
    */

    'usage' => [
        'enabled' => true,
        'reset_on_renewal' => true,
        'track_overage' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Renewal Settings
    |--------------------------------------------------------------------------
    |
    | Configure automatic subscription renewal behavior.
    |
    */

    'renewal' => [
        'auto_renew' => true,
        'notify_before_days' => 7,
        'retry_failed_renewals' => true,
        'retry_attempts' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redirects
    |--------------------------------------------------------------------------
    |
    | Configure where to redirect users for subscription-related issues.
    | These are route names that will be used by middleware.
    |
    */

    'redirect' => [
        'no_subscription' => 'home',
        'wrong_plan' => 'home',
        'no_feature' => 'home',
        'expired' => 'home',
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Configure middleware for subscription protection.
    |
    */

    'middleware' => [
        'subscribed' => \CleaniqueCoders\PackageSubscription\Http\Middleware\Subscribed::class,
        'subscribed.plan' => \CleaniqueCoders\PackageSubscription\Http\Middleware\SubscribedToPlan::class,
        'feature' => \CleaniqueCoders\PackageSubscription\Http\Middleware\Feature::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    |
    | Configure event dispatching behavior.
    |
    */

    'events' => [
        'dispatch' => true,
        'queue' => false,
    ],

];
