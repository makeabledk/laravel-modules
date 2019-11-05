<?php

namespace Makeable\LaravelModules\Commands;

use Makeable\LaravelModules\ModuleInstaller;

trait SuggestsComposerUpdate
{
    protected function suggestComposerUpdate()
    {
        if (! $this->option('no-update') && $this->confirm('Would you like to run composer update?')) {
            app(ModuleInstaller::class)->updateComposer();
        }
    }
}
