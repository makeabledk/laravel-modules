<?php

namespace Makeable\LaravelModules\Commands;

use Illuminate\Console\Command;
use Makeable\LaravelModules\Module;

class CreateSiteCommand extends CreateModuleCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:site {name} {--no-update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new site module';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->create(Module::make('sites', $this->argument('name')));
    }
}
