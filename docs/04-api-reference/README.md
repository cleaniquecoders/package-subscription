# API Reference

Complete API documentation for all components in the Package Subscription package.

## Table of Contents

1. [Models](./01-models.md) - Plan, Subscription, Usage, SubscriptionHistory
2. [Traits](./02-traits.md) - HasSubscriptions trait
3. [Services](./03-services.md) - SubscriptionService, UsageService, ProrationService
4. [Events](./04-events.md) - Subscription lifecycle events
5. [Enums](./05-enums.md) - BillingPeriod, SubscriptionStatus

## Quick Reference

### Models

| Model | Description |
|-------|-------------|
| `Plan` | Subscription plan definitions |
| `Subscription` | Active subscriptions |
| `Usage` | Feature usage tracking |
| `SubscriptionHistory` | Subscription event history |

### Traits

| Trait | Description |
|-------|-------------|
| `HasSubscriptions` | Add subscription capabilities to any model |

### Services

| Service | Description |
|---------|-------------|
| `SubscriptionService` | Subscription lifecycle management |
| `UsageService` | Feature usage tracking |
| `ProrationService` | Proration calculations |

### Events

| Event | Trigger |
|-------|---------|
| `SubscriptionCreated` | New subscription created |
| `SubscriptionRenewed` | Subscription renewed |
| `SubscriptionCancelled` | Subscription cancelled |
| `SubscriptionSuspended` | Subscription suspended |
| `SubscriptionResumed` | Subscription resumed |
| `SubscriptionExpired` | Subscription expired |
| `PlanChanged` | Plan upgraded/downgraded |
| `UsageRecorded` | Usage recorded |
| `UsageLimitExceeded` | Limit exceeded |

### Enums

| Enum | Values |
|------|--------|
| `BillingPeriod` | daily, weekly, monthly, quarterly, yearly, lifetime |
| `SubscriptionStatus` | active, on_trial, cancelled, suspended, expired, incomplete |

## Namespace

```php
CleaniqueCoders\PackageSubscription
```

## Next Steps

- Browse the [Models](./01-models.md) documentation
- Learn about the [HasSubscriptions](./02-traits.md) trait
- Explore [Services](./03-services.md) for business logic
- Set up [Events](./04-events.md) for notifications
