<?php

namespace Qwantum\Image;

use Illuminate\Support\ServiceProvider;

class ImageServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerMigrations();
    }

    protected function registerMigrations(): void
    {
        $migration_path = realpath(__DIR__ . '/../database/migrations');

        $this->loadMigrationsFrom($migration_path);
    }
}
