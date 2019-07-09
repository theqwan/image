<?php

namespace Qwantum\Image\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Qwantum\Image\Image;

class ImageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_images()
    {
        $attributes = [
            'filename' => Hash::make('filename'),
            'original_filename' => 'filename',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'path' => 'file/path/',
            'size' => '100000',
        ];

        Image::create($attributes);

        $this->assertEquals(1, Image::count());
    }
}
