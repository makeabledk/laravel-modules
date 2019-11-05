<?php

namespace Makeable\LaravelModules;

use Illuminate\Support\Str;

class Module
{
    public static $basePath;

    /**
     * @var string
     */
    protected $groupName, $name;

    /**
     * @param  string  $groupName
     * @param  string  $name
     */
    public function __construct($groupName, $name)
    {
        static::$basePath = static::$basePath ?: base_path();

        $this->groupName = $groupName;
        $this->name = $name;
    }

    /**
     * @param $name
     * @return static
     */
    public static function createService($name)
    {
        return (new static('services', $name))->create();
    }

    /**
     * @param $name
     * @return static
     */
    public static function createSite($name)
    {
        return (new static('sites', $name))->create();
    }

    /**
     * @return $this
     */
    public function create()
    {
        if (file_exists($modulePath = $this->getModulePath())) {
            throw new \BadMethodCallException('Folder already exists in: '.$modulePath);
        }

        if (! file_exists($groupPath = $this->getGroupPath())) {
            mkdir($groupPath, 0755);
        }

        mkdir($this->getModulePath(), 0755);
        mkdir($this->getModuleAppPath(), 0755);

        $this->initComposer();
        $this->initProvider();

        app(ModuleInstaller::class)->install($this);

        return $this;
    }

    // _________________________________________________________________________________________________________________

    /**
     * @param  null  $class
     * @return string
     */
    public function getNamespace($class = null)
    {
        return Str::studly($this->groupName)."\\".Str::studly($this->name).($class !== null ? "\\".$class : "");
    }

    /**
     * @return string
     */
    public function getGroupPath()
    {
        return static::$basePath.'/'.$this->groupName;
    }

    /**
     * @param  null  $file
     * @return string
     */
    public function getModulePath($file = null)
    {
        return $this->getGroupPath().'/'.$this->name.($file ? '/'.$file : '');
    }

    /**
     * @param  null  $file
     * @return string
     */
    public function getModuleAppPath($file = null)
    {
        return $this->getModulePath('app').($file ? '/'.$file : '');
    }

    /**
     * @return string
     */
    public function getPackageName()
    {
        return "{$this->groupName}/{$this->name}";
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return Str::studly($this->name).'ServiceProvider';
    }

    // _________________________________________________________________________________________________________________

    /**
     * @return void
     */
    protected function initComposer()
    {
        $template = $this->fill(static::stub('composer.json'), [
            'namespace' => addslashes($this->getNamespace('')),
            'package_name' => $this->getPackageName(),
            'provider_name' => $this->getProviderName(),
        ]);

        file_put_contents($this->getModulePath('composer.json'), $template);
    }

    /**
     * @return void
     */
    protected function initProvider()
    {
        $template = $this->fill(static::stub('ServiceProvider.php'), [
            'namespace' => $this->getNamespace(),
            'provider_name' => $this->getProviderName(),
        ]);

        file_put_contents($this->getModuleAppPath("{$this->getProviderName()}.php"), $template);
    }

    /**
     * @param $template
     * @param $data
     * @return mixed
     */
    protected function fill($template, $data)
    {
        foreach ($data as $key => $value) {
            $template = str_replace("%{$key}%", $value, $template);
        }

        return $template;
    }

    /**
     * @param $name
     * @return false|string
     */
    protected static function stub($name)
    {
        return file_get_contents(__DIR__ . "/../stubs/{$name}.stub");
    }
}
