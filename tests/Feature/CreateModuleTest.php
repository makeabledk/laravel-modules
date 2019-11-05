<?php

namespace Makeable\LaravelModules\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Makeable\LaravelModules\Module;
use Makeable\LaravelModules\ModuleInstaller;
use Makeable\LaravelModules\Tests\TestCase;

class CreateModuleTest extends TestCase
{
    /** @test * */
    public function it_can_create_a_module_through_module_class()
    {
        $installer = ModuleInstaller::fake();
        $module = Module::make('sites', 'api');

        $this->assertFalse($module->exists());

        $module->create();

        $this->assertFileExists($module->getModulePath('composer.json'));
        $this->assertFileExists($module->getModulePath('app/ApiServiceProvider.php'));
        $this->assertTrue($module->exists());
        $this->assertNotNull($installer->read("require.{$module->getPackageName()}"));
    }

    /** @test * */
    public function it_can_create_a_module_through_the_artisan_command()
    {
        $installer = ModuleInstaller::fake();
        $module = Module::make('modules', 'api');

        Artisan::call('modules:make modules/api --no-update');

        $this->assertFileExists($module->getModulePath('composer.json'));
        $this->assertFileExists($module->getModulePath('app/ApiServiceProvider.php'));
        $this->assertNotNull($installer->read("require.{$module->getPackageName()}"));
        $this->assertFalse($installer->updatedComposer);
    }

    /** @test * */
    public function it_can_create_a_site_through_the_artisan_command()
    {
        $installer = ModuleInstaller::fake();
        $module = Module::make('sites', 'api');

        Artisan::call('modules:site api --no-update');

        $this->assertFileExists($module->getModulePath('composer.json'));
        $this->assertFileExists($module->getModulePath('app/ApiServiceProvider.php'));
        $this->assertNotNull($installer->read("require.{$module->getPackageName()}"));
        $this->assertFalse($installer->updatedComposer);
    }

    /** @test * */
    public function it_can_create_a_service_through_the_artisan_command()
    {
        $installer = ModuleInstaller::fake();
        $module = Module::make('services', 'users');

        Artisan::call('modules:service users --no-update');

        $this->assertFileExists($module->getModulePath('composer.json'));
        $this->assertFileExists($module->getModulePath('app/UsersServiceProvider.php'));
        $this->assertNotNull($installer->read("require.{$module->getPackageName()}"));
        $this->assertFalse($installer->updatedComposer);
    }

    /** @test * */
    public function it_suggests_to_update_composer_after_running_artisan_command()
    {
        $installer = ModuleInstaller::fake();

        $this
            ->artisan('modules:make modules/api')
            ->expectsQuestion('Would you like to run composer update?', 'yes');

        $this->assertTrue($installer->updatedComposer);
    }
}
