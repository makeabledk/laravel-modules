<?php

namespace Makeable\LaravelModules\Tests\Feature;

use Illuminate\Support\Arr;
use Makeable\LaravelModules\Module;
use Makeable\LaravelModules\ModuleInstaller;
use Makeable\LaravelModules\Tests\TestCase;

class InstallerTest extends TestCase
{
    /** @test * */
    public function it_does_not_change_the_formatting()
    {
        $installer = ModuleInstaller::fake();
        $originalContents = file_get_contents($installer->path());
        $installer->write($installer->read());

        $this->assertEquals($originalContents, file_get_contents($installer->path()));
    }

    /** @test * */
    public function it_can_write_to_a_specific_key()
    {
        $installer = ModuleInstaller::fake();
        $properties = count($installer->read());
        $installer->write('foo', 'bar');

        $this->assertEquals($properties + 1, count($installer->read()));
    }

    /** @test * */
    public function it_adds_a_repositories_config()
    {
        $installer = ModuleInstaller::fake()->install(new Module('sites', 'web'));

        $this->assertEquals($installer->read('repositories'), [
            [
                'type' => 'path',
                'url' => './sites/web',
            ],
        ]);
    }

    /** @test * */
    public function it_appends_a_repository_to_an_existing_numeric_repositories_config()
    {
        $installer = ModuleInstaller::fake();
        $installer->write(array_merge($installer->read(), [
            'repositories' => [
                [
                    'type' => 'path',
                    'url' => './sites/web',
                ],
            ],
        ]));
        $installer->install(new Module('sites', 'api'));

        $this->assertEquals(2, count($installer->read('repositories')));
    }

    /** @test * */
    public function it_sorts_repositories_alphabetically()
    {
        $installer = ModuleInstaller::fake();

        $installer->install(new Module('sites', 'web'));
        $installer->install(new Module('sites', 'api'));

        $this->assertEquals('./sites/api', $installer->read('repositories.0.url'));
        $this->assertEquals('./sites/web', $installer->read('repositories.1.url'));
    }

    /** @test * */
    public function it_appends_a_repository_to_an_existing_associative_repositories_config()
    {
        $installer = ModuleInstaller::fake();
        $installer->write(array_merge($installer->read(), [
            'repositories' => [
                'sites/web' => [
                    'type' => 'path',
                    'url' => './sites/web',
                ],
            ],
        ]));

        $this->assertEquals(
            Arr::get($installer->install(new Module('sites', 'api'))->read(), 'repositories'),
            [
                'sites/web' => [
                    'type' => 'path',
                    'url' => './sites/web',
                ],
                'sites/api' => [
                    'type' => 'path',
                    'url' => './sites/api',
                ],
            ]
        );
    }
}
