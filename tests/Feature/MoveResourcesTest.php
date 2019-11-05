<?php

namespace Makeable\LaravelModules\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Makeable\LaravelModules\Module;
use Makeable\LaravelModules\ModuleInstaller;
use Makeable\LaravelModules\Tests\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MoveResourcesTest extends TestCase
{

    /** @test * */
    public function it_requires_a_given_module()
    {
        tap($this)
            ->expectException(HttpException::class)
            ->prepareApp()
            ->artisan('modules:move User --app-path='.$this->tmp('app'));
    }

    /** @test * */
    public function it_can_move_files_to_a_new_module()
    {
        $this
            ->prepareApp()
            ->artisan('modules:move User --app-path='.$this->tmp('app'));

        ;
//        $installer = ModuleInstaller::fake();
//        $site = Module::createSite('api');

//        copy()


        $this->assertFileExists($site->getModulePath('composer.json'));
        $this->assertFileExists($site->getModulePath('app/ApiServiceProvider.php'));
        $this->assertTrue($installer->wasInstalled);
    }

    /**
     * @return $this
     */
    protected function prepareApp()
    {
        shell_exec('cp -R '.$this->stub('app').' '.$this->tmp('app'));

        return $this;
    }
}
