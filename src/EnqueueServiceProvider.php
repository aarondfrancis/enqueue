<?php

namespace AaronFrancis\Enqueue;

use AaronFrancis\Enqueue\Commands\EnqueueCommand;
use Illuminate\Support\ServiceProvider;

class EnqueueServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                EnqueueCommand::class,
            ]);
        }
    }
}
