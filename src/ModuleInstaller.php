<?php

namespace Makeable\LaravelModules;

use Illuminate\Support\Arr;
use Makeable\LaravelModules\Tests\FakeModuleInstaller;

class ModuleInstaller
{
    /**
     * @var string
     */
    protected $composerPath;

    /**
     * @param  string|null  $composerPath
     */
    public function __construct($composerPath = null)
    {
        $this->composerPath = $composerPath ?: base_path('/composer.json');
    }

    /**
     * Fake the installer
     *
     * @return FakeModuleInstaller
     */
    public static function fake()
    {
        app()->singleton(static::class, FakeModuleInstaller::class);

        return app(static::class);
    }

    /**
     * Install the module in root project
     *
     * @param  \Makeable\LaravelModules\Module  $module
     * @return \Makeable\LaravelModules\ModuleInstaller
     */
    public function install(Module $module)
    {
        $this->addRepository($module);
        $this->requirePackage($module);

        return $this;
    }

    /**
     * @param  \Makeable\LaravelModules\Module  $module
     */
    protected function addRepository(Module $module)
    {
        $contents = $this->read();
        $repositories = Arr::get($contents, 'repositories', []);

        $repository = [
            'type' => 'path',
            'url' => './' . $module->getPackageName()
        ];

        $contents['repositories'] = array_merge($repositories, Arr::isAssoc($repositories)
            ? [$module->getPackageName() => $repository]
            : [$repository]
        );

        $this->write($contents);
    }

    /**
     * @param  \Makeable\LaravelModules\Module  $module
     */
    protected function requirePackage(Module $module)
    {
        shell_exec("composer require {$module->getPackageName()}:'*@dev'");
    }

    /**
     * @return string
     */
    public function path()
    {
        return $this->composerPath;
    }

    /**
     * @return array
     */
    public function read()
    {
        return json_decode(file_get_contents($this->path()), true);
    }

    /**
     * Write object to composer.json.
     * Append empty line as per PSR spec.
     *
     * @param  array  $contents
     */
    public function write($contents)
    {
        file_put_contents($this->path(), json_encode($contents, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES) . "\n");
    }
}
