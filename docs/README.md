# Package Subscription Documentation

> A comprehensive Laravel package for managing subscription plans and subscriptions in SaaS applications.

## Table of Contents

### [01. Architecture](./01-architecture/README.md)

Understand the system design, components, and patterns used in this package.

- [Overview](./01-architecture/01-overview.md) - Package structure and core concepts
- [Data Models](./01-architecture/02-data-models.md) - Database schema and relationships
- [Services](./01-architecture/03-services.md) - Service layer architecture

### [02. Development](./02-development/README.md)

Get started with development, installation, and configuration.

- [Getting Started](./02-development/01-getting-started.md) - Installation and setup
- [Configuration](./02-development/02-configuration.md) - Package configuration options
- [Testing](./02-development/03-testing.md) - Testing patterns and commands

### [03. Usage](./03-usage/README.md)

Learn how to use the package features in your application.

- [Plan Management](./03-usage/01-plan-management.md) - Creating and managing plans
- [Subscriptions](./03-usage/02-subscriptions.md) - Subscription lifecycle management
- [Usage Tracking](./03-usage/03-usage-tracking.md) - Track and enforce feature limits
- [Access Control](./03-usage/04-access-control.md) - Middleware and Blade directives

### [04. API Reference](./04-api-reference/README.md)

Complete API documentation for all components.

- [Models](./04-api-reference/01-models.md) - Plan, Subscription, Usage models
- [Traits](./04-api-reference/02-traits.md) - HasSubscriptions trait
- [Services](./04-api-reference/03-services.md) - SubscriptionService, UsageService
- [Events](./04-api-reference/04-events.md) - Subscription lifecycle events
- [Enums](./04-api-reference/05-enums.md) - BillingPeriod, SubscriptionStatus

## Quick Links

- [Installation Guide](./02-development/01-getting-started.md)
- [Basic Usage Example](./03-usage/02-subscriptions.md)
- [Configuration Options](./02-development/02-configuration.md)
- [API Reference](./04-api-reference/README.md)

## Package Features

| Feature | Description |
|---------|-------------|
| **Plan Management** | Create flexible pricing tiers with features and limits |
| **Subscription Lifecycle** | Subscribe, renew, cancel, suspend, and resume subscriptions |
| **Usage Tracking** | Monitor and enforce usage quotas |
| **Trial Periods** | Configurable trial periods for new subscriptions |
| **Plan Changes** | Upgrades and downgrades with proration support |
| **Access Control** | Middleware, Blade directives, and feature gates |
| **Events** | Event-driven architecture for subscription changes |

## Contributing

Please see [CONTRIBUTING](../CONTRIBUTING.md) for details on how to contribute to this package.
