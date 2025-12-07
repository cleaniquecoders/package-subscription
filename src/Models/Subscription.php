<?php

namespace CleaniqueCoders\PackageSubscription\Models;

use CleaniqueCoders\PackageSubscription\Enums\SubscriptionStatus;
use CleaniqueCoders\PackageSubscription\Events\PlanChanged;
use CleaniqueCoders\PackageSubscription\Events\SubscriptionCancelled;
use CleaniqueCoders\PackageSubscription\Events\SubscriptionExpired;
use CleaniqueCoders\PackageSubscription\Events\SubscriptionRenewed;
use CleaniqueCoders\PackageSubscription\Events\SubscriptionResumed;
use CleaniqueCoders\PackageSubscription\Events\SubscriptionSuspended;
use CleaniqueCoders\PackageSubscription\Services\ProrationService;
use CleaniqueCoders\PackageSubscription\Services\UsageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $subscribable_type
 * @property int $subscribable_id
 * @property int $plan_id
 * @property SubscriptionStatus $status
 * @property \Illuminate\Support\Carbon|null $trial_ends_at
 * @property \Illuminate\Support\Carbon $starts_at
 * @property \Illuminate\Support\Carbon $ends_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property \Illuminate\Support\Carbon|null $suspended_at
 * @property \Illuminate\Support\Carbon|null $grace_ends_at
 * @property float $price
 * @property string $billing_period
 * @property array<string, mixed>|null $snapshot
 * @property array<string, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Plan $plan
 * @property-read Model $subscribable
 */
class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subscribable_type',
        'subscribable_id',
        'plan_id',
        'status',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'cancelled_at',
        'suspended_at',
        'grace_ends_at',
        'price',
        'billing_period',
        'snapshot',
        'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'suspended_at' => 'datetime',
        'grace_ends_at' => 'datetime',
        'snapshot' => 'array',
        'metadata' => 'array',
        'status' => SubscriptionStatus::class,
    ];

    /**
     * Get the subscribable model (User, Team, etc.)
     */
    public function subscribable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the plan for this subscription
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(config('package-subscription.models.plan', Plan::class));
    }

    /**
     * Get the usage records for this subscription
     */
    public function usages(): HasMany
    {
        return $this->hasMany(config('package-subscription.models.usage', Usage::class));
    }

    /**
     * Get the history records for this subscription
     */
    public function history(): HasMany
    {
        return $this->hasMany(config('package-subscription.models.history', SubscriptionHistory::class));
    }

    /**
     * Scope a query to only include active subscriptions
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereIn('status', ['active', 'on_trial']);
    }

    /**
     * Scope a query to only include subscriptions on trial
     */
    public function scopeOnTrial(Builder $query): void
    {
        $query->where('status', 'on_trial')
            ->where('trial_ends_at', '>', now());
    }

    /**
     * Scope a query to only include cancelled subscriptions
     */
    public function scopeCancelled(Builder $query): void
    {
        $query->where('status', 'cancelled');
    }

    /**
     * Scope a query to only include expired subscriptions
     */
    public function scopeExpired(Builder $query): void
    {
        $query->where('status', 'expired');
    }

    /**
     * Scope a query to subscriptions for a specific plan
     */
    public function scopeForPlan(Builder $query, Plan|string $plan): void
    {
        if ($plan instanceof Plan) {
            $query->where('plan_id', $plan->id);
        } else {
            $query->whereHas('plan', fn ($q) => $q->where('slug', $plan));
        }
    }

    /**
     * Check if the subscription is active
     */
    public function isActive(): bool
    {
        return $this->status->isActive() && ! $this->hasEnded();
    }

    /**
     * Check if the subscription is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === SubscriptionStatus::CANCELLED;
    }

    /**
     * Check if the subscription is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === SubscriptionStatus::SUSPENDED;
    }

    /**
     * Check if the subscription is expired
     */
    public function isExpired(): bool
    {
        return $this->status === SubscriptionStatus::EXPIRED || $this->hasEnded();
    }

    /**
     * Check if the subscription is on trial
     */
    public function isOnTrial(): bool
    {
        return $this->status === SubscriptionStatus::ON_TRIAL
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if the subscription is on grace period
     */
    public function isOnGracePeriod(): bool
    {
        return $this->grace_ends_at && $this->grace_ends_at->isFuture();
    }

    /**
     * Check if the subscription has ended
     */
    public function hasEnded(): bool
    {
        /** @phpstan-ignore-next-line */
        return $this->ends_at && $this->ends_at->isPast();
    }

    /**
     * Cancel the subscription
     */
    public function cancel(bool $immediately = false): self
    {
        if ($immediately) {
            $this->update([
                'status' => SubscriptionStatus::CANCELLED,
                'cancelled_at' => now(),
                'ends_at' => now(),
            ]);
        } else {
            $this->update([
                'cancelled_at' => now(),
            ]);
        }

        event(new SubscriptionCancelled($this, $immediately));

        return $this;
    }

    /**
     * Cancel the subscription at the end of the billing period
     */
    public function cancelAtPeriodEnd(): self
    {
        return $this->cancel(false);
    }

    /**
     * Suspend the subscription
     */
    public function suspend(?string $reason = null): self
    {
        $this->update([
            'status' => SubscriptionStatus::SUSPENDED,
            'suspended_at' => now(),
            'metadata' => array_merge($this->metadata ?? [], ['suspension_reason' => $reason]),
        ]);

        event(new SubscriptionSuspended($this, $reason));

        return $this;
    }

    /**
     * Resume a suspended or cancelled subscription
     */
    public function resume(): self
    {
        $this->update([
            'status' => SubscriptionStatus::ACTIVE,
            'suspended_at' => null,
            'cancelled_at' => null,
        ]);

        event(new SubscriptionResumed($this));

        return $this;
    }

    /**
     * Renew the subscription
     */
    public function renew(): self
    {
        $previousEndDate = $this->ends_at;
        $newEndDate = $this->plan->calculateNextBillingDate($this->ends_at ?? now());

        $this->update([
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => $this->ends_at ?? now(),
            'ends_at' => $newEndDate,
            'cancelled_at' => null,
        ]);

        event(new SubscriptionRenewed($this, $previousEndDate, $newEndDate));

        return $this;
    }

    /**
     * Mark the subscription as expired
     */
    public function expire(): self
    {
        $this->update([
            'status' => SubscriptionStatus::EXPIRED,
        ]);

        event(new SubscriptionExpired($this));

        return $this;
    }

    /**
     * Upgrade to a higher plan
     */
    public function upgradeTo(Plan $plan, array $options = []): self
    {
        return $this->switchTo($plan, array_merge($options, ['type' => 'upgrade']));
    }

    /**
     * Downgrade to a lower plan
     */
    public function downgradeTo(Plan $plan, array $options = []): self
    {
        return $this->switchTo($plan, array_merge($options, ['type' => 'downgrade']));
    }

    /**
     * Switch to a different plan
     */
    public function switchTo(Plan $plan, array $options = []): self
    {
        $oldPlan = $this->plan;
        $prorationAmount = 0;

        if ($options['prorate'] ?? config('package-subscription.proration.enabled', true)) {
            $prorationService = app(ProrationService::class);
            $prorationAmount = $prorationService->calculate($this, $plan);
        }

        $this->update([
            'plan_id' => $plan->id,
            'price' => $plan->price,
            'billing_period' => $plan->billing_period->value,
            'snapshot' => $plan->features,
        ]);

        // Record in history
        $this->history()->create([
            'from_plan_id' => $oldPlan->id,
            'to_plan_id' => $plan->id,
            'event_type' => $options['type'] ?? 'switch',
            'proration_amount' => $prorationAmount,
        ]);

        event(new PlanChanged($this, $oldPlan, $plan, $options['type'] ?? 'switch', $prorationAmount));

        return $this;
    }

    /**
     * Check if user can use a specific feature
     */
    public function canUseFeature(string $feature): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        return $this->plan->hasFeature($feature);
    }

    /**
     * Get the value of a specific feature
     */
    public function getFeatureValue(string $feature): mixed
    {
        // Use snapshot if available (locked at subscription time)
        if ($this->snapshot && isset($this->snapshot[$feature])) {
            return $this->snapshot[$feature];
        }

        // Fall back to current plan features
        return $this->plan->getFeatureValue($feature);
    }

    /**
     * Get the limit for a specific feature
     */
    public function getFeatureLimit(string $feature): ?int
    {
        $value = $this->getFeatureValue($feature);

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    /**
     * Record usage for a feature
     */
    public function recordUsage(string $feature, float $amount): Usage
    {
        return app(UsageService::class)->record($this, $feature, $amount);
    }

    /**
     * Increment usage for a feature
     */
    public function incrementUsage(string $feature, float $amount = 1): Usage
    {
        return app(UsageService::class)->increment($this, $feature, $amount);
    }

    /**
     * Decrement usage for a feature
     */
    public function decrementUsage(string $feature, float $amount = 1): Usage
    {
        return app(UsageService::class)->decrement($this, $feature, $amount);
    }

    /**
     * Set usage for a feature
     */
    public function setUsage(string $feature, float $amount): Usage
    {
        return app(UsageService::class)->set($this, $feature, $amount);
    }

    /**
     * Get current usage for a feature
     */
    public function getUsage(string $feature): float
    {
        return app(UsageService::class)->get($this, $feature);
    }

    /**
     * Get remaining usage for a feature
     */
    public function getRemainingUsage(string $feature): ?float
    {
        return app(UsageService::class)->getRemaining($this, $feature);
    }

    /**
     * Get usage percentage for a feature
     */
    public function getUsagePercentage(string $feature): float
    {
        $limit = $this->getFeatureLimit($feature);
        if (! $limit) {
            return 0;
        }

        $used = $this->getUsage($feature);

        return min(100, ($used / $limit) * 100);
    }

    /**
     * Check if usage exceeds limit for a feature
     */
    public function exceedsLimit(string $feature): bool
    {
        $limit = $this->getFeatureLimit($feature);
        if ($limit === null) {
            return false; // No limit = never exceeds
        }

        return $this->getUsage($feature) >= $limit;
    }

    /**
     * Check if within limit for a feature
     */
    public function withinLimit(string $feature, float $proposed = 0): bool
    {
        return app(UsageService::class)->checkLimit($this, $feature, $proposed);
    }

    /**
     * Reset usage for all or specific feature
     */
    public function resetUsage(?string $feature = null): void
    {
        app(UsageService::class)->reset($this, $feature);
    }
}
