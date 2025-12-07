<?php

namespace CleaniqueCoders\PackageSubscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $subscription_id
 * @property int|null $from_plan_id
 * @property int|null $to_plan_id
 * @property string $event_type
 * @property float|null $proration_amount
 * @property array<string, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Subscription $subscription
 * @property-read Plan|null $fromPlan
 * @property-read Plan|null $toPlan
 */
class SubscriptionHistory extends Model
{
    use HasFactory;

    protected $table = 'subscription_history';

    protected $fillable = [
        'subscription_id',
        'from_plan_id',
        'to_plan_id',
        'event_type',
        'proration_amount',
        'metadata',
    ];

    protected $casts = [
        'proration_amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Get the subscription for this history record
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(config('package-subscription.models.subscription', Subscription::class));
    }

    /**
     * Get the plan the subscription changed from
     */
    public function fromPlan(): BelongsTo
    {
        return $this->belongsTo(config('package-subscription.models.plan', Plan::class), 'from_plan_id');
    }

    /**
     * Get the plan the subscription changed to
     */
    public function toPlan(): BelongsTo
    {
        return $this->belongsTo(config('package-subscription.models.plan', Plan::class), 'to_plan_id');
    }

    /**
     * Check if this is an upgrade
     */
    public function isUpgrade(): bool
    {
        return $this->event_type === 'upgrade';
    }

    /**
     * Check if this is a downgrade
     */
    public function isDowngrade(): bool
    {
        return $this->event_type === 'downgrade';
    }

    /**
     * Check if this is a cancellation
     */
    public function isCancellation(): bool
    {
        return $this->event_type === 'cancelled';
    }
}
