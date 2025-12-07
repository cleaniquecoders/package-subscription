<?php

namespace CleaniqueCoders\PackageSubscription\Http\Resources;

use CleaniqueCoders\PackageSubscription\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Subscription
 */
class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plan' => new PlanResource($this->whenLoaded('plan')),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'is_active' => $this->isActive(),
            'is_cancelled' => $this->isCancelled(),
            'is_suspended' => $this->isSuspended(),
            'is_expired' => $this->isExpired(),
            'is_on_trial' => $this->isOnTrial(),
            'is_on_grace_period' => $this->isOnGracePeriod(),
            'trial_ends_at' => $this->trial_ends_at?->toISOString(),
            'starts_at' => $this->starts_at->toISOString(),
            'ends_at' => $this->ends_at->toISOString(),
            'cancelled_at' => $this->cancelled_at?->toISOString(),
            'suspended_at' => $this->suspended_at?->toISOString(),
            'grace_ends_at' => $this->grace_ends_at?->toISOString(),
            'price' => $this->price,
            'billing_period' => $this->billing_period,
            'features' => $this->snapshot,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
