<?php

namespace Qwantum\Image\Tests;

use Illuminate\Support\Facades\Storage;
use Qwantum\Image\ImageServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        foreach (Storage::directories() as $directory) {
            Storage::deleteDirectory($directory);
        }

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ImageServiceProvider::class,
            TestServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application   $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testdb');
        $app['config']->set('database.connections.testdb', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $app['config']->set('qwantum.image.imagemagick_path', '/usr/local/opt/imagemagick@6/bin/');
        $app['config']->set('qwantum.image.folder_to_model', [
            'pages' => Page::class,
        ]);
    }
}
