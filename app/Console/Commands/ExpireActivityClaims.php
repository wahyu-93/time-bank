<?php

namespace App\Console\Commands;

use App\Services\ActivityExpiryService;
use Illuminate\Console\Command;

class ExpireActivityClaims extends Command
{
    protected $signature = 'activities:expire';

    protected $description = 'Expire pending activity claims from previous days';

    public function handle(ActivityExpiryService $service): int
    {
        $count = $service->expirePending();

        $this->info("Expired {$count} activity claim(s).");

        return self::SUCCESS;
    }
}