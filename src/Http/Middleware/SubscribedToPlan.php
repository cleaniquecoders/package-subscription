<?php

namespace CleaniqueCoders\PackageSubscription\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscribedToPlan
{
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next, string $plan): Response
    {
        $user = $request->user();

        /** @phpstan-ignore-next-line */
        if (! $user || ! method_exists($user, 'subscribedTo')) {
            abort(403, 'User model must use HasSubscriptions trait');
        }

        if (! $user->subscribedTo($plan)) {
            $redirectRoute = config('package-subscription.redirect.wrong_plan', 'home');

            return redirect()->route($redirectRoute)
                ->with('error', "You need the {$plan} plan to access this resource.");
        }

        return $next($request);
    }
}
