<?php

namespace Qwantum\Image\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Qwantum\Image\Image;

class ImageableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bind_image_form_article()
    {
        $attributes = [
            'filename' => Hash::make('filename'),
            'original_filename' => 'filename',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'path' => 'file/path/',
            'size' => '100000',
        ];

        $image = Image::create($attributes);

        $article = Article::create(['title' => 'title']);

        $article->images()->save($image);

        $this->assertTrue(count(Article::first()->images) == 1);
    }
}
