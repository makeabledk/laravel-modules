<?php

namespace Makeable\LaravelModules\Commands;

use Makeable\LaravelModules\Module;

class CreateServiceCommand extends CreateModuleCommand
{
    protected $signature = 'modules:service {name} {--no-update}';

    protected $description = 'Create a new service module';

    public function handle()
    {
        $this->create(Module::service($this->argument('name')));
    }
}
