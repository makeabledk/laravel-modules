<?php

namespace Makeable\LaravelModules\Commands;

use Illuminate\Console\Command;
use Makeable\LaravelModules\Module;

class CreateModuleCommand extends Command
{
    use SuggestsComposerUpdate;

    protected $signature = 'modules:make {name} {--assets} {--routes} {--no-update}';

    protected $description = 'Create a new custom module in the format {type}/{name}';

    public function handle()
    {
        $parts = explode('/', $this->argument('name'));

        abort_unless(400, count($parts) === 2, 'Invalid module name. Please type a name in the format "{type}/{name}".');

        $this->create(
            Module::make(...$parts)
                ->assets($this->option('assets'))
                ->routes($this->option('routes'))
        );
    }

    /**
     * @param  \Makeable\LaravelModules\Module  $module
     */
    protected function create(Module $module)
    {
        $this->comment("Created {$module->create()->getPackageName()} module and updated project composer.json.");

        $this->suggestComposerUpdate();
    }
}
