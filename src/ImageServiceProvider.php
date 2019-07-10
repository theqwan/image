<?php

namespace Qwantum\Image;

use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Support\ServiceProvider;
use Qwantum\Image\Routing\Router;

class ImageServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRouter();
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerMigrations();
    }

    protected function registerMigrations()
    {
        $migration_path = realpath(__DIR__ . '/../database/migrations');

        $this->loadMigrationsFrom($migration_path);
    }

    protected function registerRouter()
    {
        $this->app->singleton('Image.router', function ($app) {
            return new Router(app(Registrar::class));
        });
    }
}
