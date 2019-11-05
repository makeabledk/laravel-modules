<?php

namespace Makeable\LaravelModules\Commands;

use Illuminate\Console\Command;
use Makeable\LaravelModules\Module;

class CreateServiceCommand extends CreateModuleCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:service {name} {--no-update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service module';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->create(Module::make('services', $this->argument('name')));
    }
}
