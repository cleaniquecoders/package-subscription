# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a comprehensive Laravel package for managing subscription plans and subscriptions in SaaS applications (`cleaniquecoders/package-subscription`). It provides an easy-to-configure solution for implementing package-based subscription management, allowing developers to create flexible pricing tiers, manage user subscriptions, track usage, and handle the complete subscription lifecycle.

The package follows the standard Laravel package structure using Spatie's Laravel Package Tools.

**Namespace:** `CleaniqueCoders\PackageSubscription`

**Key Capabilities:**

- Plan management with flexible tiers and features
- Complete subscription lifecycle (subscribe, renew, cancel, suspend, resume)
- Usage tracking and quota management
- Trial periods and grace periods
- Plan upgrades/downgrades with proration
- Multi-tenancy support (user and team-based subscriptions)
- Feature access control with gates, middleware, and Blade directives
- Event-driven architecture for subscription state changes

## Development Commands

### Testing

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage
```

### Code Quality

```bash
# Run PHPStan static analysis (Level 5)
composer analyse

# Fix code style with Laravel Pint
composer format
```

### Package Discovery

```bash
# Regenerate package discovery (runs automatically after composer install/update)
composer prepare
```

## Architecture

### Package Structure

This package uses `Spatie\LaravelPackageTools\PackageServiceProvider` as its foundation. The service provider (src/PackageSubscriptionServiceProvider.php:11-24) configures:

- Config file: `package-subscription`
- Views
- Migration: `create_package_subscription_table`
- Command: `PackageSubscriptionCommand`

### Expected Components (Based on Features)

The package should implement these core components to support subscription management:

**Models:**

- `Plan` - Subscription plans with pricing, features, and limits
- `Subscription` - User/Team subscriptions with lifecycle management
- `Usage` - Track feature usage against plan limits

**Traits:**

- `HasSubscriptions` - Add to User/Team models for subscription functionality

**Middleware:**

- `Subscribed` - Check for active subscription
- `SubscribedToPlan` - Verify specific plan subscription
- `Feature` - Verify feature access based on plan

**Events:**

- `SubscriptionCreated` - Fired when new subscription is created
- `SubscriptionCancelled` - Fired when subscription is cancelled
- `SubscriptionRenewed` - Fired when subscription renews
- `SubscriptionExpired` - Fired when subscription expires
- `PlanChanged` - Fired when user changes plans

**Blade Directives:**

- `@subscribed` - Check subscription status
- `@subscribedToPlan('plan')` - Check specific plan
- `@feature('feature')` - Check feature access

**Service Provider**: `PackageSubscriptionServiceProvider` - Registers middleware, events, Blade directives, and commands

### Testing Setup

- **Framework**: Pest PHP 4.x
- **Test Base**: All tests use `TestCase` configured in tests/Pest.php:5
- **Orchestra Testbench**: Used for Laravel package testing environment
- **Architecture Tests**: tests/ArchTest.php enforces no debugging functions (dd, dump, ray) in production code

### Static Analysis

PHPStan is configured with:

- Level 5 analysis
- Paths analyzed: src, config, database
- Octane compatibility checking enabled
- Model property checking enabled
- Baseline file: phpstan-baseline.neon

## Package Installation for End Users

```bash
composer require cleaniquecoders/package-subscription

# Publish migrations
php artisan vendor:publish --tag="package-subscription-migrations"
php artisan migrate

# Publish config
php artisan vendor:publish --tag="package-subscription-config"

# Publish views (optional)
php artisan vendor:publish --tag="package-subscription-views"
```

## Requirements

- PHP ^8.4
- Laravel ^11.0 || ^12.0
- Spatie Laravel Package Tools ^1.16
