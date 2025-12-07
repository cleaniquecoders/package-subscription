<?php

namespace CleaniqueCoders\PackageSubscription\Http\Resources;

use CleaniqueCoders\PackageSubscription\Models\Usage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Usage
 */
class UsageResource extends JsonResource
{
    /**
     * Transform the resource into an array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'feature' => $this->feature,
            'used' => $this->used,
            'limit' => $this->limit,
            'remaining' => $this->getRemainingAmount(),
            'percentage' => $this->getPercentageUsed(),
            'is_exceeded' => $this->isExceeded(),
            'is_unlimited' => $this->limit === null,
            'valid_until' => $this->valid_until?->toISOString(),
            'reset_at' => $this->reset_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
