<?php

namespace Qwantum\Image\Tests;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class TestServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/web.php');

        Relation::morphMap([
            'Page' => Page::class,
        ]);
    }
}
