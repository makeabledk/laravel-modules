<?php

namespace Makeable\LaravelModules\Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Makeable\LaravelFactory\Factory;
use Makeable\LaravelFactory\FactoryServiceProvider;
use Makeable\LaravelModules\Module;
use Makeable\LaravelModules\ModulesServiceProvider;

class TestCase extends BaseTestCase
{
//    use RefreshDatabase;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        putenv('APP_ENV=testing');
//        putenv('DB_CONNECTION=mysql'); // using sqlite will cause rounding issues in score calculation

        $app = require __DIR__.'/../vendor/laravel/laravel/bootstrap/app.php';
        $app->useEnvironmentPath(__DIR__.'/..');
//        $app->useDatabasePath(__DIR__);
        $app->make(Kernel::class)->bootstrap();
        $app->register(ModulesServiceProvider::class);

        Module::$basePath = $this->tmp();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->emptyTmp();
    }

    protected function emptyTmp()
    {
        foreach (glob($this->tmp('*')) as $file) {
            shell_exec('rm -Rf '.$file);
        }
    }

    /**
     * @param  null  $file
     * @return string
     */
    protected function tmp($file = null)
    {
        return __DIR__.'/tmp'.($file ? '/'.$file : '');
    }
}
