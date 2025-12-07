<?php

namespace CleaniqueCoders\PackageSubscription\Models;

use CleaniqueCoders\Traitify\Concerns\InteractsWithUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $uuid
 * @property int $subscription_id
 * @property string $feature
 * @property float $used
 * @property float|null $limit
 * @property \Illuminate\Support\Carbon|null $valid_until
 * @property \Illuminate\Support\Carbon|null $reset_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Subscription $subscription
 */
class Usage extends Model
{
    use HasFactory, InteractsWithUuid;

    protected $fillable = [
        'subscription_id',
        'feature',
        'used',
        'limit',
        'valid_until',
        'reset_at',
    ];

    protected $casts = [
        'used' => 'decimal:4',
        'limit' => 'decimal:4',
        'valid_until' => 'datetime',
        'reset_at' => 'datetime',
    ];

    /**
     * Get the subscription for this usage record
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(config('package-subscription.models.subscription', Subscription::class));
    }

    /**
     * Increment the usage by a given amount
     */
    public function incrementUsage(float $amount = 1): self
    {
        $this->used += $amount;
        $this->save();

        return $this;
    }

    /**
     * Decrement the usage by a given amount
     */
    public function decrementUsage(float $amount = 1): self
    {
        $this->used = max(0, $this->used - $amount);
        $this->save();

        return $this;
    }

    /**
     * Reset the usage counter
     */
    public function reset(): self
    {
        $this->update([
            'used' => 0,
            'reset_at' => now(),
        ]);

        return $this;
    }

    /**
     * Check if the usage record has expired
     */
    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    /**
     * Get the remaining amount before hitting the limit
     */
    public function getRemainingAmount(): ?float
    {
        if ($this->limit === null) {
            return null; // Unlimited
        }

        return max(0, $this->limit - $this->used);
    }

    /**
     * Get the percentage of usage
     */
    public function getPercentageUsed(): float
    {
        if (! $this->limit) {
            return 0;
        }

        return min(100, ($this->used / $this->limit) * 100);
    }

    /**
     * Check if the usage has exceeded the limit
     */
    public function isExceeded(): bool
    {
        if ($this->limit === null) {
            return false; // No limit
        }

        return $this->used >= $this->limit;
    }
}
