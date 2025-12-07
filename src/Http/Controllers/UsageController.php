<?php

namespace CleaniqueCoders\PackageSubscription\Http\Controllers;

use CleaniqueCoders\PackageSubscription\Http\Resources\UsageResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsageController
{
    /**
     * Display user's current usage statistics
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->activeSubscription();

        if (! $subscription) {
            return response()->json([
                'message' => 'No active subscription',
            ], 422);
        }

        $usages = $subscription->usages;

        return response()->json([
            'data' => UsageResource::collection($usages),
            'subscription' => [
                'plan' => $subscription->plan->name,
                'features' => $subscription->plan->features,
            ],
        ]);
    }

    /**
     * Get usage for a specific feature
     */
    public function show(Request $request, string $feature): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasActiveSubscription()) {
            return response()->json([
                'message' => 'No active subscription',
            ], 422);
        }

        $usage = $user->getUsage($feature);
        $limit = $user->getFeatureLimit($feature);
        $remaining = $user->getRemainingUsage($feature);
        $percentage = $user->getUsagePercentage($feature);

        return response()->json([
            'data' => [
                'feature' => $feature,
                'used' => $usage,
                'limit' => $limit,
                'remaining' => $remaining,
                'percentage' => $percentage,
                'exceeded' => $user->exceedsLimit($feature),
            ],
        ]);
    }
}
