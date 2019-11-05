<?php

namespace Makeable\LaravelModules\Commands;

use Illuminate\Console\Command;
use Makeable\LaravelModules\Module;

class CreateSiteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:site {name}';

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
        $module = Module::createSite($this->argument('name'));

        $this->info("Created {$module->getPackageName()} module. Happy coding!");
    }
}
