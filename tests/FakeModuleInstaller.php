<?php

namespace Makeable\LaravelModules\Tests;

use Makeable\LaravelModules\Module;
use Makeable\LaravelModules\ModuleInstaller;

class FakeModuleInstaller extends ModuleInstaller
{
    /**
     * @var bool
     */
    public $wasInstalled = false;

    public function __construct()
    {
        @unlink($composerPath = __DIR__.'/tmp/composer.json');

        copy(__DIR__.'/stubs/composer.json', $composerPath);

        parent::__construct($composerPath);
    }

    /**
     * @param  \Makeable\LaravelModules\Module  $module
     */
    protected function requirePackage(Module $module)
    {
        $this->wasInstalled = true;
    }
}
