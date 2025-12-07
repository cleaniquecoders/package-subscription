<?php

namespace CleaniqueCoders\PackageSubscription\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Subscribed
{
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next, ?string $plan = null): Response
    {
        $user = $request->user();

        /** @phpstan-ignore-next-line */
        if (! $user || ! method_exists($user, 'hasActiveSubscription')) {
            abort(403, 'User model must use HasSubscriptions trait');
        }

        if (! $user->hasActiveSubscription()) {
            $redirectRoute = config('package-subscription.redirect.no_subscription', 'home');

            return redirect()->route($redirectRoute)
                ->with('error', 'You need an active subscription to access this resource.');
        }

        if ($plan && ! $user->subscribedTo($plan)) {
            $redirectRoute = config('package-subscription.redirect.wrong_plan', 'home');

            return redirect()->route($redirectRoute)
                ->with('error', "You need to be subscribed to the {$plan} plan.");
        }

        return $next($request);
    }
}
