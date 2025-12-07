<?php

namespace CleaniqueCoders\PackageSubscription\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CleaniqueCoders\PackageSubscription\PackageSubscription
 */
class PackageSubscription extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CleaniqueCoders\PackageSubscription\PackageSubscription::class;
    }
}
