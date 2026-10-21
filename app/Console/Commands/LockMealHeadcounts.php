<?php

namespace App\Console\Commands;

use App\Services\MealLifecycleService;
use Illuminate\Console\Command;

class LockMealHeadcounts extends Command
{
    protected $signature = 'meals:lock-headcounts';

    protected $description = 'Transition meals between open, locked, serving and closed, pin lanes and warn students';

    public function handle(MealLifecycleService $lifecycle): int
    {
        $changed = $lifecycle->syncAll();
        $warnings = $lifecycle->sendCutoffWarnings();

        $this->info("Meal states synced: {$changed} transition(s), {$warnings} cutoff warning(s).");

        return self::SUCCESS;
    }
}
