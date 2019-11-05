<?php

namespace Makeable\LaravelModules\Commands;

use Illuminate\Console\Command;
use Makeable\LaravelModules\Module;

class MoveResourcesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:move {resources}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move resources to a module';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        dd($this->argument('test'));

//        $module = Module::createService($this->argument('name'));

//        $this->info("Created {$module->getPackageName()} module. Happy coding!");
    }
}
