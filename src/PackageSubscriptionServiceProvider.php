<?php

namespace CleaniqueCoders\PackageSubscription;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use CleaniqueCoders\PackageSubscription\Commands\PackageSubscriptionCommand;

class PackageSubscriptionServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('package-subscription')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_package_subscription_table')
            ->hasCommand(PackageSubscriptionCommand::class);
    }
}
