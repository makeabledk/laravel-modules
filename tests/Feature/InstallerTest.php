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
    public function it_adds_a_repositories_config()
    {
        $installer = ModuleInstaller::fake()->install(new Module('sites', 'web'));
        $config = $installer->read();

        $this->assertEquals($config['repositories'], [
            [
                'type' => 'path',
                'url' => './sites/web'
            ]
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
                    'url' => './sites/web'
                ]
            ]
        ]));

        $this->assertEquals(
            Arr::get($installer->install(new Module('sites', 'api'))->read(), 'repositories'),
            [
                [
                    'type' => 'path',
                    'url' => './sites/web'
                ],
                [
                    'type' => 'path',
                    'url' => './sites/api'
                ],
            ]
        );
    }

    /** @test * */
    public function it_appends_a_repository_to_an_existing_associative_repositories_config()
    {
        $installer = ModuleInstaller::fake();
        $installer->write(array_merge($installer->read(), [
            'repositories' => [
                'sites/web' => [
                    'type' => 'path',
                    'url' => './sites/web'
                ]
            ]
        ]));

        $this->assertEquals(
            Arr::get($installer->install(new Module('sites', 'api'))->read(), 'repositories'),
            [
                'sites/web' => [
                    'type' => 'path',
                    'url' => './sites/web'
                ],
                'sites/api' => [
                    'type' => 'path',
                    'url' => './sites/api'
                ],
            ]
        );
    }
}
