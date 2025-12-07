<?php

namespace CleaniqueCoders\PackageSubscription\Enums;

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case ON_TRIAL = 'on_trial';
    case CANCELLED = 'cancelled';
    case SUSPENDED = 'suspended';
    case EXPIRED = 'expired';
    case INCOMPLETE = 'incomplete';

    /**
     * Get the human-readable label for the subscription status
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::ON_TRIAL => 'On Trial',
            self::CANCELLED => 'Cancelled',
            self::SUSPENDED => 'Suspended',
            self::EXPIRED => 'Expired',
            self::INCOMPLETE => 'Incomplete',
        };
    }

    /**
     * Check if the subscription status is considered active
     */
    public function isActive(): bool
    {
        return in_array($this, [self::ACTIVE, self::ON_TRIAL]);
    }

    /**
     * Check if the subscription can access features
     */
    public function canAccess(): bool
    {
        return in_array($this, [self::ACTIVE, self::ON_TRIAL]);
    }

    /**
     * Get the CSS class for the status badge
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'badge-success',
            self::ON_TRIAL => 'badge-info',
            self::CANCELLED => 'badge-warning',
            self::SUSPENDED => 'badge-secondary',
            self::EXPIRED => 'badge-danger',
            self::INCOMPLETE => 'badge-light',
        };
    }

    /**
     * Get the color for the status
     */
    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'green',
            self::ON_TRIAL => 'blue',
            self::CANCELLED => 'orange',
            self::SUSPENDED => 'gray',
            self::EXPIRED => 'red',
            self::INCOMPLETE => 'yellow',
        };
    }
}
