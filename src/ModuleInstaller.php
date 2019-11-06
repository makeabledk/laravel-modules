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
     * Fake the installer.
     *
     * @return FakeModuleInstaller
     */
    public static function fake()
    {
        app()->singleton(static::class, FakeModuleInstaller::class);

        return app(static::class);
    }

    /**
     * Install the module in root project.
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
     * Run composer update command.
     */
    public function updateComposer()
    {
        shell_exec('composer update');
    }

    /**
     * @return string
     */
    public function path()
    {
        return $this->composerPath;
    }

    /**
     * @param  null  $key
     * @return array
     */
    public function read($key = null)
    {
        $contents = json_decode(file_get_contents($this->path()), true);

        return $key ? data_get($contents, $key) : $contents;
    }

    /**
     * Write object to composer.json.
     * Append empty line as per PSR spec.
     *
     * @param $key
     * @param  array  $contents
     */
    public function write($key, $contents = null)
    {
        if ($contents === null) {
            $contents = $key;
            $key = null;
        }

        if ($key !== null) {
            [$partial, $contents] = [$contents, $this->read()];

            data_set($contents, $key, $partial);
        }

        file_put_contents($this->path(), json_encode($contents, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES)."\n");
    }

    /**
     * @param  \Makeable\LaravelModules\Module  $module
     */
    protected function addRepository(Module $module)
    {
        $repositories = $this->read('repositories') ?: [];
        $repository = [
            'type' => 'path',
            'url' => './'.$module->getPackageName(),
        ];

        $this->write(
            'repositories',
            Arr::isAssoc($repositories)
                ? array_merge($repositories, [$module->getPackageName() => $repository])
                : value(function () use ($repositories, $repository) {
                    array_push($repositories, $repository);

                    return array_values(Arr::sort($repositories, 'url'));
                })
        );
    }

    /**
     * @param  \Makeable\LaravelModules\Module  $module
     */
    protected function requirePackage(Module $module)
    {
        $this->write('require', array_merge($this->read('require'), [
            $module->getPackageName() => '*@dev',
        ]));
    }
}
