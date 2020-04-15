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

    /** @test */
    public function test_isNeedGenerateThumbnails()
    {
        $this->assertEquals(true, Article::isNeedGenerateThumbnails());
        $this->assertEquals(false, Page::isNeedGenerateThumbnails());
    }

    /** @test */
    public function test_getGenerateThumbnailSettings()
    {
        $this->assertEquals(config('qwantum.image.thumbnails'), Article::getGenerateThumbnailSettings());
        $this->assertEquals(['big' => [1000, 1000]], Page::getGenerateThumbnailSettings());
    }

    /** @test */
    public function test_isNeedResizeToMaxWidth()
    {
        $this->assertEquals(true, Article::isNeedResizeToMaxWidth());
        $this->assertEquals(false, Page::isNeedResizeToMaxWidth());
    }
}
