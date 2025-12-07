<?php

namespace CleaniqueCoders\PackageSubscription;

use CleaniqueCoders\PackageSubscription\Commands\CheckExpiredSubscriptionsCommand;
use CleaniqueCoders\PackageSubscription\Commands\PackageSubscriptionCommand;
use CleaniqueCoders\PackageSubscription\Commands\RenewSubscriptionsCommand;
use CleaniqueCoders\PackageSubscription\Commands\ResetUsageCommand;
use CleaniqueCoders\PackageSubscription\Http\Middleware\Feature;
use CleaniqueCoders\PackageSubscription\Http\Middleware\Subscribed;
use CleaniqueCoders\PackageSubscription\Http\Middleware\SubscribedToPlan;
use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PackageSubscriptionServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('package-subscription')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations([
                'create_plans_table',
                'create_subscriptions_table',
                'create_usages_table',
                'create_subscription_history_table',
            ])
            ->hasCommands([
                PackageSubscriptionCommand::class,
                CheckExpiredSubscriptionsCommand::class,
                RenewSubscriptionsCommand::class,
                ResetUsageCommand::class,
            ]);
    }

    public function packageBooted(): void
    {
        // Register middleware
        $router = $this->app['router'];
        $router->aliasMiddleware('subscribed', Subscribed::class);
        $router->aliasMiddleware('subscribed.plan', SubscribedToPlan::class);
        $router->aliasMiddleware('feature', Feature::class);

        // Register Blade directives
        $this->registerBladeDirectives();
    }

    protected function registerBladeDirectives(): void
    {
        // @subscribed directive
        Blade::if('subscribed', function () {
            return auth()->check() && auth()->user()->hasActiveSubscription();
        });

        // @subscribedToPlan directive
        Blade::if('subscribedToPlan', function (string $plan) {
            return auth()->check() && auth()->user()->subscribedTo($plan);
        });

        // @feature directive
        Blade::if('feature', function (string $feature) {
            return auth()->check() && auth()->user()->canUseFeature($feature);
        });

        // Negative directives
        Blade::if('notSubscribed', function () {
            return ! auth()->check() || ! auth()->user()->hasActiveSubscription();
        });

        Blade::if('notSubscribedToPlan', function (string $plan) {
            return ! auth()->check() || ! auth()->user()->subscribedTo($plan);
        });

        Blade::if('notFeature', function (string $feature) {
            return ! auth()->check() || ! auth()->user()->canUseFeature($feature);
        });
    }
}
