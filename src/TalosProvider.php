<?php
namespace Elison\Talos;

use Elison\Talos\Console\Commands\GenerateRequest;
use Illuminate\Support\ServiceProvider;

class TalosProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateRequest::class,
            ]);
        }
    }

    public function register()
    {
        //
    }
}
