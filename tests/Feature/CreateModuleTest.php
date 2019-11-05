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
        $site = Module::createSite('api');

        $this->assertFileExists($site->getModulePath('composer.json'));
        $this->assertFileExists($site->getModulePath('app/ApiServiceProvider.php'));
        $this->assertTrue($installer->wasInstalled);
    }

    /** @test * */
    public function it_can_create_a_site_through_the_artisan_command()
    {
        $installer = ModuleInstaller::fake();
        $site = new Module('sites', 'api'); // just for path reference

        Artisan::call('modules:site api');

        $this->assertFileExists($site->getModulePath('composer.json'));
        $this->assertFileExists($site->getModulePath('app/ApiServiceProvider.php'));
        $this->assertTrue($installer->wasInstalled);
    }

    /** @test * */
    public function it_can_create_a_service_through_the_artisan_command()
    {
        $installer = ModuleInstaller::fake();
        $site = new Module('services', 'users'); // just for path reference

        Artisan::call('modules:service users');

        $this->assertFileExists($site->getModulePath('composer.json'));
        $this->assertFileExists($site->getModulePath('app/UsersServiceProvider.php'));
        $this->assertTrue($installer->wasInstalled);
    }
}
