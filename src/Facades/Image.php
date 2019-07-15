<?php

namespace Qwantum\Image\Facades;

use Illuminate\Support\Facades\Facade;

class Image extends Facade
{
    /**
     * @param array $options
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public static function route(array $options = [])
    {
        static::$app->make('Image.router')->route($options);
    }
}
