# Changelog

All notable changes to `package-subscription` will be documented in this file.

## Fixes - 2025-12-07

### Release Note for v1.0.1

#### v1.0.1 (2025-12-07)

##### Fixed

- Lifetime Billing Period - Fixed `BillingPeriod::addTo()` to return `null` for lifetime subscriptions instead of adding 100 years to
  the date. This prevents potential issues with MySQL DATETIME limits and provides clearer semantics where null means "never expires".

##### Changed

- `BillingPeriod::addTo()` now returns `?Carbon (nullable)` instead of Carbon
- `Plan::calculateNextBillingDate()` now returns `?Carbon (nullable)` instead of Carbon
- Subscriptions with lifetime billing will now have `ends_at` set to null in the database

##### Technical Details

For lifetime subscriptions:

- `ends_at` is now null (previously ~100 years in the future)
- Database migration already supports nullable ends_at field
- No data migration required
- All tests pass with no breaking changes to functionality

## First Release - 2025-12-07

### Release Notes

#### v1.0.0 (2025-12-07)

##### Initial Release

A Laravel package for managing subscriptions, plans, and feature usage tracking.

###### Features

- **Plan Management** - Create and manage subscription plans with customizable features and limits
- **Subscription Lifecycle** - Handle subscriptions with support for trials, renewals, and cancellations
- **Usage Tracking** - Track and enforce feature usage limits per subscription
- **Billing Periods** - Support for daily, weekly, monthly, and yearly billing cycles
- **Access Control** - Middleware and Blade directives for plan-based authorization
- **Event System** - Subscription lifecycle events for integration with notifications and workflows

###### Included

- `HasSubscriptions` trait for subscribable models
- `SubscriptionService` and `UsageService` for business logic
- Database migrations for plans, subscriptions, and usage tracking
- Configuration file for customization
- Comprehensive test suite
