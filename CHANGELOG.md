# Changelog

All notable changes to `package-subscription` will be documented in this file.

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
