<?php

namespace CleaniqueCoders\PackageSubscription\Http\Resources;

use CleaniqueCoders\PackageSubscription\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Plan
 */
class PlanResource extends JsonResource
{
    /**
     * Transform the resource into an array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'formatted_price' => $this->formatted_price,
            'billing_period' => $this->billing_period->value,
            'billing_period_label' => $this->billing_period->label(),
            'billing_interval' => $this->billing_interval,
            'trial_period_days' => $this->trial_period_days,
            'grace_period_days' => $this->grace_period_days,
            'features' => $this->features,
            'is_active' => $this->is_active,
            'is_free' => $this->isFree(),
            'has_trial' => $this->hasTrial(),
            'is_lifetime' => $this->isLifetime(),
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
