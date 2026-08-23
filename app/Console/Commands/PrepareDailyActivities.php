<?php

namespace App\Console\Commands;

use App\Services\ActivityExpiryService;
use Illuminate\Console\Command;

class PrepareDailyActivities extends Command
{
    protected $signature = 'activities:prepare';

    protected $description = 'Create daily activity claims';

    public function handle(ActivityExpiryService $service): int
    {
        $count = $service->createDailyClaims();

        $this->info("Created {$count} daily activity claim(s).");

        return self::SUCCESS;
    }
}