<?php

namespace Makeable\LaravelModules;

use Illuminate\Support\ServiceProvider;
use Makeable\LaravelModules\Commands\CreateModuleCommand;
use Makeable\LaravelModules\Commands\CreateServiceCommand;
use Makeable\LaravelModules\Commands\CreateSiteCommand;
use Makeable\LaravelModules\Commands\MoveResourcesCommand;

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
            CreateModuleCommand::class,
            CreateServiceCommand::class,
            CreateSiteCommand::class,
            MoveResourcesCommand::class,
        ]);
    }
}
