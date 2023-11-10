<?php

namespace Shabeersha\EventTracker\Providers;

use Illuminate\Support\ServiceProvider;

class EventTrackerProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(realpath(__DIR__ . '/../routes/web.php'));
    }
}
