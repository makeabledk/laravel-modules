<?php

namespace Makeable\LaravelModules;

use Illuminate\Support\ServiceProvider;
use Makeable\LaravelModules\Commands\CreateServiceCommand;
use Makeable\LaravelModules\Commands\CreateSiteCommand;

class ModulesServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands([
            CreateServiceCommand::class,
            CreateSiteCommand::class,
        ]);
    }
}
