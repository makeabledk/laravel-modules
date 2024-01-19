<?php

namespace Makeable\LaravelModules\Commands;

use Illuminate\Console\Command;
use Makeable\LaravelModules\Module;

class CreateSiteCommand extends CreateModuleCommand
{
    protected $signature = 'modules:site {name} {--no-update}';

    protected $description = 'Create a new site module';

    public function handle()
    {
        $this->create(Module::site($this->argument('name')));
    }
}
