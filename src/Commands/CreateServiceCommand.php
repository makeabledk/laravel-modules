<?php

namespace Makeable\LaravelModules\Commands;

use Illuminate\Console\Command;
use Makeable\LaravelModules\Module;

class CreateServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:service {name}';

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
        $module = Module::createService($this->argument('name'));

        $this->info("Created {$module->getPackageName()} module. Happy coding!");
    }
}
