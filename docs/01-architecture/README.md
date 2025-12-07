# Architecture

This section covers the system design, core components, and architectural patterns used in the Package Subscription package.

## Table of Contents

1. [Overview](./01-overview.md) - Package structure, namespace, and core concepts
2. [Data Models](./02-data-models.md) - Database schema, models, and relationships
3. [Services](./03-services.md) - Service layer architecture and patterns

## Overview

The package follows Laravel's standard package structure using Spatie's Laravel Package Tools. It implements a clean architecture with:

- **Models** - Eloquent models for Plans, Subscriptions, Usage, and History
- **Services** - Business logic encapsulated in service classes
- **Traits** - Reusable functionality via the `HasSubscriptions` trait
- **Events** - Event-driven architecture for subscription lifecycle
- **Middleware** - Route protection based on subscription status
- **Commands** - Artisan commands for subscription maintenance

## Key Concepts

| Concept | Description |
|---------|-------------|
| **Plan** | A subscription tier with pricing, features, and limits |
| **Subscription** | A user's active subscription to a plan |
| **Usage** | Tracked feature consumption against plan limits |
| **Subscribable** | Any model that can have subscriptions (User, Team) |

## Component Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    Your Application                          │
├─────────────────────────────────────────────────────────────┤
│  User/Team Model                                            │
│  └── uses HasSubscriptions trait                            │
├─────────────────────────────────────────────────────────────┤
│                Package Subscription Layer                    │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │   Models    │  │  Services   │  │   Events    │         │
│  │ - Plan      │  │ - Subscrip- │  │ - Created   │         │
│  │ - Subscrip- │  │   tionSvc   │  │ - Cancelled │         │
│  │   tion      │  │ - UsageSvc  │  │ - Renewed   │         │
│  │ - Usage     │  │ - Proration │  │ - etc.      │         │
│  │ - History   │  │   Svc       │  │             │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │ Middleware  │  │  Commands   │  │    Blade    │         │
│  │ - Subscribed│  │ - Check     │  │ - @subscri- │         │
│  │ - Feature   │  │   Expired   │  │   bed       │         │
│  │ - Plan      │  │ - Renew     │  │ - @feature  │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
├─────────────────────────────────────────────────────────────┤
│                      Database Layer                          │
│  plans | subscriptions | usages | subscription_history       │
└─────────────────────────────────────────────────────────────┘
```

## Next Steps

- Learn about the [data models and relationships](./02-data-models.md)
- Understand the [service layer](./03-services.md)
