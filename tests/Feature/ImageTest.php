<?php

namespace Qwantum\Image\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    /** @test */
    public function imageable_test()
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

        $this->assertTrue(Image::first()->imageable == Article::first());
    }

    /** @test */
    public function image_url_test()
    {
        $response = $this->json('POST', route('images.upload', 'test'), [
            'upload' => $fake_image = UploadedFile::fake()->image('avatar.jpg'),
        ])->assertStatus(200);

        $image = Image::first();

        $filename = $image->filename;
        $extension = $image->extension;

        $now = now();
        $year = $now->year;
        $month = sprintf("%02d", $now->month);

        $this->assertEquals("http://localhost/{$year}/{$month}/test/{$filename}.{$extension}", $image->url);
    }

    /** @test */
    public function image_thumbnail_url_test()
    {
        $response = $this->json('POST', route('images.upload', 'test'), [
            'upload' => $fake_image = UploadedFile::fake()->image('avatar.jpg'),
        ])->assertStatus(200);

        $image = Image::first();

        $filename = $image->filename;
        $extension = $image->extension;

        $now = now();
        $year = $now->year;
        $month = sprintf("%02d", $now->month);

        foreach (array_keys(config('qwantum.image.thumbnails')) as $thumbnail_name) {
            $this->assertEquals("http://localhost/{$year}/{$month}/test/{$filename}_{$thumbnail_name}.{$extension}", $image->{"{$thumbnail_name}_url"});
        }
    }
}
