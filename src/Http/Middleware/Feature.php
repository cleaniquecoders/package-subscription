<?php

namespace CleaniqueCoders\PackageSubscription\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Feature
{
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        /** @phpstan-ignore-next-line */
        if (! $user || ! method_exists($user, 'canUseFeature')) {
            abort(403, 'User model must use HasSubscriptions trait');
        }

        if (! $user->canUseFeature($feature)) {
            $redirectRoute = config('package-subscription.redirect.no_feature', 'home');

            return redirect()->route($redirectRoute)
                ->with('error', "Your plan doesn't include access to {$feature}.");
        }

        return $next($request);
    }
}
