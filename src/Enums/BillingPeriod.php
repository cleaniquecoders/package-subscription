<?php

namespace CleaniqueCoders\PackageSubscription\Enums;

use Carbon\Carbon;

enum BillingPeriod: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
    case YEARLY = 'yearly';
    case LIFETIME = 'lifetime';

    /**
     * Get the interval value for the billing period
     */
    public function interval(): int
    {
        return match ($this) {
            self::DAILY => 1,
            self::WEEKLY => 7,
            self::MONTHLY => 30,
            self::QUARTERLY => 90,
            self::YEARLY => 365,
            self::LIFETIME => 0,
        };
    }

    /**
     * Get the human-readable label for the billing period
     */
    public function label(): string
    {
        return match ($this) {
            self::DAILY => 'Daily',
            self::WEEKLY => 'Weekly',
            self::MONTHLY => 'Monthly',
            self::QUARTERLY => 'Quarterly',
            self::YEARLY => 'Yearly',
            self::LIFETIME => 'Lifetime',
        };
    }

    /**
     * Add the billing period to a given date
     */
    public function addTo(Carbon $date, int $count = 1): Carbon
    {
        return match ($this) {
            self::DAILY => $date->copy()->addDays($count),
            self::WEEKLY => $date->copy()->addWeeks($count),
            self::MONTHLY => $date->copy()->addMonths($count),
            self::QUARTERLY => $date->copy()->addMonths($count * 3),
            self::YEARLY => $date->copy()->addYears($count),
            self::LIFETIME => $date->copy()->addYears(100), // Effectively never expires
        };
    }

    /**
     * Check if this is a lifetime billing period
     */
    public function isLifetime(): bool
    {
        return $this === self::LIFETIME;
    }

    /**
     * Get the number of days in this billing period (approximate for monthly/yearly)
     */
    public function days(): int
    {
        return $this->interval();
    }
}
