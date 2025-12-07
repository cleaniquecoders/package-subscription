<?php

namespace CleaniqueCoders\PackageSubscription\Http\Controllers;

use CleaniqueCoders\PackageSubscription\Http\Resources\PlanResource;
use CleaniqueCoders\PackageSubscription\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController
{
    /**
     * Display a listing of active plans
     */
    public function index(Request $request): JsonResponse
    {
        $plans = Plan::active()
            ->ordered()
            ->get();

        return response()->json([
            'data' => PlanResource::collection($plans),
        ]);
    }

    /**
     * Display the specified plan
     */
    public function show(Plan $plan): JsonResponse
    {
        return response()->json([
            'data' => new PlanResource($plan),
        ]);
    }
}
