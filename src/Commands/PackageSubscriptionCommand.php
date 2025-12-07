<?php

namespace CleaniqueCoders\PackageSubscription\Commands;

use Illuminate\Console\Command;

class PackageSubscriptionCommand extends Command
{
    public $signature = 'package-subscription';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
