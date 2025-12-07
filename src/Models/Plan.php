<?php

namespace CleaniqueCoders\PackageSubscription\Models;

use Carbon\Carbon;
use CleaniqueCoders\PackageSubscription\Enums\BillingPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $description
 * @property float $price
 * @property BillingPeriod $billing_period
 * @property int $billing_interval
 * @property int $trial_period_days
 * @property int $grace_period_days
 * @property array<string, mixed> $features
 * @property array<string, mixed>|null $metadata
 * @property bool $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'price',
        'billing_period',
        'billing_interval',
        'trial_period_days',
        'grace_period_days',
        'features',
        'metadata',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'trial_period_days' => 'integer',
        'grace_period_days' => 'integer',
        'billing_interval' => 'integer',
        'features' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'billing_period' => BillingPeriod::class,
    ];

    /**
     * Get all subscriptions for this plan
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(config('package-subscription.models.subscription', Subscription::class));
    }

    /**
     * Get only active subscriptions for this plan
     */
    public function activeSubscriptions(): HasMany
    {
        return $this->subscriptions()->where('status', 'active');
    }

    /**
     * Scope a query to only include active plans
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope a query to order plans by sort order
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('price');
    }

    /**
     * Check if the plan has a specific feature
     */
    public function hasFeature(string $feature): bool
    {
        $features = $this->features ?? [];

        return array_key_exists($feature, $features);
    }

    /**
     * Get the value of a specific feature
     */
    public function getFeatureValue(string $feature): mixed
    {
        $features = $this->features ?? [];

        return $features[$feature] ?? null;
    }

    /**
     * Check if a feature is enabled (boolean check)
     */
    public function isFeatureEnabled(string $feature): bool
    {
        $value = $this->getFeatureValue($feature);

        return $value === true || $value === 'true' || $value === 1;
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
     * Calculate the next billing date from a given date
     */
    public function calculateNextBillingDate(?Carbon $from = null): Carbon
    {
        $from = $from ?? now();

        return $this->billing_period->addTo($from, $this->billing_interval);
    }

    /**
     * Check if this is a free plan
     */
    public function isFree(): bool
    {
        return $this->price == 0;
    }

    /**
     * Check if this plan has a trial period
     */
    public function hasTrial(): bool
    {
        return $this->trial_period_days > 0;
    }

    /**
     * Check if this is a lifetime plan
     */
    public function isLifetime(): bool
    {
        return $this->billing_period->isLifetime();
    }

    /**
     * Get the plan price formatted
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2);
    }

    /**
     * Get all features as a collection
     */
    public function getAllFeatures(): array
    {
        return $this->features ?? [];
    }
}
