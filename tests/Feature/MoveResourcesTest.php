<?php

namespace Makeable\LaravelModules\Tests\Feature;

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
            ->artisan('modules:move User --force --app-path='.$this->tmp('app'));
    }

    /** @test * */
    public function it_can_move_files_to_a_new_module()
    {
        $installer = ModuleInstaller::fake();

        $this->prepareApp()->artisan('modules:move User --service users --force --no-update --app-path='.$this->tmp('app'));

        $this->assertFileExists($this->tmp('app/Jobs/ProcessPodcastJob.php'));
        $this->assertFileExists($model = $this->tmp('services/users/app/User.php'));
        $this->assertFileExists($controller = $this->tmp('services/users/app/Http/Controllers/UserController.php'));
        $this->assertFileNotExists($this->tmp('services/users/app/Jobs/ProcessPodcastJob.php'));
//        $this->assertFileDoesNotExist($this->tmp('services/users/app/Jobs/ProcessPodcastJob.php')); // PhpUnit 10

        $this->assertNamespace($model, 'Services\\Users');
        $this->assertNamespace($controller, 'Services\\Users\\Http\\Controllers');

        $this->assertFalse($installer->updatedComposer);
    }

    /** @test * */
    public function it_can_move_files_to_existing_modules()
    {
        ModuleInstaller::fake();
        Module::make('services', 'users')->create();

        $this->prepareApp()->artisan('modules:move User --service users --force --app-path='.$this->tmp('app'));
        $this->assertFileExists($this->tmp('services/users/app/User.php'));
    }

    /** @test * */
    public function it_suggests_to_update_composer_after_module_was_created()
    {
        $installer = ModuleInstaller::fake();

        $this
            ->prepareApp()
            ->artisan('modules:move User --service users --force --app-path='.$this->tmp('app'))
            ->expectsQuestion('Would you like to run composer update?', 'yes');

        $this->assertTrue($installer->updatedComposer);
    }

    /**
     * @return $this
     */
    protected function prepareApp()
    {
        shell_exec('cp -R '.$this->stub('app').' '.$this->tmp('app'));

        return $this;
    }

    /**
     * @param $file
     * @param $namespace
     */
    protected function assertNamespace($file, $namespace)
    {
        $contents = file_get_contents($file);

        $this->assertStringContainsString('namespace '.$namespace.';', $contents);
    }
}
