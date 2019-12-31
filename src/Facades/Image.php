<?php

namespace Qwantum\Image\Facades;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Facade;
use Qwantum\Image\Services\ImageService;

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

    /**
     * @param UploadedFile $uploadedFile
     * @param string $folder
     * @param string|null $role
     * @param string|null $location
     * @param int|null $manual_order
     * @return \Qwantum\Image\Image|\Illuminate\Database\Eloquent\Model
     */
    public static function saveImageByUploadedFile(UploadedFile $uploadedFile, $folder, $role = null, $location = null, $manual_order = null)
    {
        return (new ImageService)->saveImage($uploadedFile, $folder, $role, $location, $manual_order);
    }
}
