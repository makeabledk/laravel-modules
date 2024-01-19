<?php

namespace Makeable\LaravelModules;

class Stub
{
    /**
     * Path to vendor/bin folder.
     *
     * @var string
     */
    public static $binPath;

    /**
     * @var \Makeable\LaravelModules\Module
     */
    protected $module;

    /**
     * @param  \Makeable\LaravelModules\Module  $module
     */
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    /**
     * @param  \Makeable\LaravelModules\Module  $module
     * @return static
     */
    public static function create(Module $module)
    {
        $stub = (new static($module))
            ->package()
            ->composer()
            ->provider();

        if ($module->routes) {
            $stub->routes();
        }

        if ($module->assets) {
            $stub->assets();
        }

        return $stub;
    }

    /**
     * Create package folder.
     *
     * @return $this
     */
    public function package()
    {
        if (! file_exists($groupPath = $this->module->getGroupPath())) {
            $this->folder($groupPath);
        }

        $this->folder($this->module->getModulePath());
        $this->folder($this->module->getModuleAppPath());

        return $this;
    }

    /**
     * Publish composer.json.
     *
     * @return $this
     */
    public function composer()
    {
        $template = $this->stub('composer.json', [
            'namespace' => addslashes($this->module->getNamespace('')),
            'package_name' => $this->module->getPackageName(),
            'provider_name' => $this->module->getProviderName(),
        ]);

        $this->write($this->module->getModulePath('composer.json'), $template);

        return $this;
    }

    /**
     * Publish provider.
     *
     * @return $this
     */
    public function provider()
    {
        $template = $this->stub('ServiceProvider.php', [
            'namespace' => $this->module->getNamespace(),
            'provider_name' => $this->module->getProviderName(),
            'snippet_load_views' => trim($this->module->assets ? $this->stub('snippet_load_views', [
                'views_namespace' => $this->module->name,
            ]) : ''),
            'snippet_load_routes' => trim($this->module->routes ? $this->stub('snippet_load_routes') : ''),
            'snippet_load_routes_helper' => trim($this->module->routes ? $this->stub('snippet_load_routes_helper', [
                'controller_namespace' => $this->module->getNamespace('Http\Controllers'),
                'routes_filename' => "{$this->module->name}.php",
            ]) : ''),
        ]);

        $this->write($this->module->getModuleAppPath("{$this->module->getProviderName()}.php"), $template, true);

        return $this;
    }

    /**
     * Publish routes file.
     */
    public function routes()
    {
        $this->folder(dirname($routesFile = $this->module->getModulePath("routes/{$this->module->name}.php")));

        $this->write($routesFile, $this->stub('routes.php', [
            'middleware_name' => $this->module->name,
        ]));
    }

    public function assets()
    {
        $this->folder($this->module->getModulePath('resources'));
        $this->folder($css = $this->module->getModulePath('resources/css'));
        $this->folder($js = $this->module->getModulePath('resources/js'));
        $this->folder($this->module->getModulePath('resources/views'));

        $this->write("{$css}/{$this->module->name}.css");
        $this->write("{$js}/{$this->module->name}.js");
    }

    /**
     * @param  $path
     */
    protected function folder($path)
    {
        mkdir($path, 0755);
    }

    /**
     * @param  $name
     * @param  array  $data
     * @return false|string
     */
    protected function stub($name, $data = [])
    {
        $template = file_get_contents(__DIR__."/../stubs/{$name}.stub");

        foreach ($data as $key => $value) {
            $template = str_replace("%{$key}%", $value, $template);
        }

        return $template;
    }

    /**
     * @param  string  $path
     * @param  string  $contents
     * @param  bool  $lint
     * @return void
     */
    protected function write($path, $contents = '', $lint = false)
    {
        file_put_contents($path, $contents);

        if ($lint) {
            // Asynchronously run linting: https://stackoverflow.com/questions/222414/asynchronous-shell-exec-in-php
            shell_exec((static::$binPath ?: base_path('vendor/bin')).'/php-cs-fixer fix '.$path.' --config '.__DIR__.'/../.php_cs.dist &> /dev/null &');
        }
    }
}
