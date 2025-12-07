<?php

namespace CleaniqueCoders\PackageSubscription\Http\Controllers;

use CleaniqueCoders\PackageSubscription\Http\Resources\SubscriptionResource;
use CleaniqueCoders\PackageSubscription\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController
{
    /**
     * Display user's subscriptions
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscriptions = $user->subscriptions()->with('plan')->latest()->get();

        return response()->json([
            'data' => SubscriptionResource::collection($subscriptions),
        ]);
    }

    /**
     * Subscribe to a plan
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'with_trial' => 'boolean',
        ]);

        $user = $request->user();
        $plan = Plan::findOrFail($validated['plan_id']);

        if ($user->hasActiveSubscription()) {
            return response()->json([
                'message' => 'You already have an active subscription',
            ], 422);
        }

        $subscription = $user->subscribeTo($plan, [
            'with_trial' => $validated['with_trial'] ?? true,
        ]);

        return response()->json([
            'message' => 'Successfully subscribed to plan',
            'data' => new SubscriptionResource($subscription->load('plan')),
        ], 201);
    }

    /**
     * Cancel the subscription
     */
    public function cancel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'immediately' => 'boolean',
        ]);

        $user = $request->user();
        $subscription = $user->cancelSubscription($validated['immediately'] ?? false);

        if (! $subscription) {
            return response()->json([
                'message' => 'No active subscription to cancel',
            ], 422);
        }

        return response()->json([
            'message' => 'Subscription cancelled successfully',
            'data' => new SubscriptionResource($subscription->load('plan')),
        ]);
    }

    /**
     * Resume a cancelled subscription
     */
    public function resume(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->resumeSubscription();

        if (! $subscription) {
            return response()->json([
                'message' => 'No cancelled subscription to resume',
            ], 422);
        }

        return response()->json([
            'message' => 'Subscription resumed successfully',
            'data' => new SubscriptionResource($subscription->load('plan')),
        ]);
    }

    /**
     * Upgrade to a different plan
     */
    public function upgrade(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'prorate' => 'boolean',
        ]);

        $user = $request->user();
        $newPlan = Plan::findOrFail($validated['plan_id']);

        if (! $user->hasActiveSubscription()) {
            return response()->json([
                'message' => 'No active subscription to upgrade',
            ], 422);
        }

        $subscription = $user->upgradeTo($newPlan, [
            'prorate' => $validated['prorate'] ?? true,
        ]);

        return response()->json([
            'message' => 'Plan upgraded successfully',
            'data' => new SubscriptionResource($subscription->load('plan')),
        ]);
    }

    /**
     * Downgrade to a different plan
     */
    public function downgrade(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'prorate' => 'boolean',
        ]);

        $user = $request->user();
        $newPlan = Plan::findOrFail($validated['plan_id']);

        if (! $user->hasActiveSubscription()) {
            return response()->json([
                'message' => 'No active subscription to downgrade',
            ], 422);
        }

        $subscription = $user->downgradeTo($newPlan, [
            'prorate' => $validated['prorate'] ?? true,
        ]);

        return response()->json([
            'message' => 'Plan downgraded successfully',
            'data' => new SubscriptionResource($subscription->load('plan')),
        ]);
    }
}
