<?php

namespace Qwantum\Image\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Qwantum\Image\Facades\ImageStorageUrl;
use Qwantum\Image\Facades\Image as ImageFacade;
use Qwantum\Image\Image;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function upload_image_test()
    {
        $response = $this->json('POST', route('images.upload', 'test'), [
            'upload' => $fake_image = UploadedFile::fake()->image('avatar.jpg'),
        ])->assertStatus(200);

        $filename = pathinfo($fake_image->hashName(), PATHINFO_FILENAME);
        $extension = $fake_image->getClientOriginalExtension();

        Storage::assertExists(date('Y').'/'.date('m').'/test/'.$filename.'.'.$extension);
    }

    /** @test */
    public function save_uploaded_image()
    {
        $fake_image = UploadedFile::fake()->image('avatar.jpg');

        $image = ImageFacade::saveImageByUploadedFile($fake_image, 'test');

        $this->assertInstanceOf(Image::class, $image);
    }

    /** @test */
    public function upload_type_validate_test()
    {
        $response = $this->json('POST', route('images.upload', 'test'), [
            'upload' => UploadedFile::fake()->create('document.pdf'),
        ])->assertStatus(422);

        $this->assertTrue($response->exception instanceof ValidationException);
    }

    /** @test */
    public function upload_height_width_validate_test()
    {
        $response = $this->json('POST', route('images.upload', 'test'), [
            'upload' => UploadedFile::fake()->image('avatar.jpg', 200, 300),
            'width' => 300,
            'height' => 300,
        ])->assertStatus(422);

        $this->assertEquals('Upload Failed. Image resolution must be 300px x 300px.', $response->getData()->message);

        $response = $this->json('POST', route('images.upload', 'test'), [
            'upload' => UploadedFile::fake()->image('avatar.jpg', 300, 300),
            'width' => 300,
            'height' => 300,
        ])->assertStatus(200);
    }

    /** @test */
    public function upload_image_will_create_image_model_test()
    {
        $response = $this->json('POST', route('images.upload', 'test'), [
            'upload' => $fake_image = UploadedFile::fake()->image('avatar.jpg'),
            'role' => 'cover',
        ])->assertStatus(200);

        $image = Image::first();

        $expected = [
            'id' => 1,
            'imageable_id' => null,
            'imageable_type' => null,
            'role' => 'cover',
            'filename' => $expected_filename = pathinfo($fake_image->hashName(), PATHINFO_FILENAME),
            'original_filename' => pathinfo($fake_image->getClientOriginalName(), PATHINFO_FILENAME),
            'mime_type' => $fake_image->getClientMimeType(),
            'extension' => $expected_extension = $fake_image->getClientOriginalExtension(),
            'path' => $expected_path = date('Y').'/'.date('m').'/test/',
            'size' => $fake_image->getSize(),
            'url' => ImageStorageUrl::to("{$expected_path}{$expected_filename}.{$expected_extension}"),
        ];

        $actual = [
            'id' => $image->id,
            'imageable_id' => $image->imageable_id,
            'imageable_type' => $image->imageable_type,
            'role' => $image->role,
            'filename' => $image->filename,
            'original_filename' => $image->original_filename,
            'mime_type' => $image->mime_type,
            'extension' => $image->extension,
            'path' => $image->path,
            'size' => $image->size,
            'url' => $image->url,
        ];

        $this->assertEquals($expected, $actual);

        Storage::assertExists($image->path_with_filename);
    }

    /** @test */
    public function keep_original_image_test()
    {
        Config::set('qwantum.image.keep_original', true);

        $response = $this->json('POST', route('images.upload', 'test'), [
            'upload' => $fake_image = UploadedFile::fake()->image('avatar.jpg'),
            'role' => 'cover',
        ])->assertStatus(200);

        $image = Image::first();

        Storage::assertExists($image->original_path_with_filename);
    }

    /** @test */
    public function thumbnails_test()
    {
        Config::set('qwantum.image.thumbnails', [
            'small' => [50, 50],
            'medium' => [null, 100],
            'large' => [300, null],
            'super_big' => [1280, null],
        ]);

        Config::set('qwantum.image.max_width', 600);

        $response = $this->json('POST', route('images.upload', 'test'), [
            'upload' => $fake_image = UploadedFile::fake()->image('avatar.jpg', 1200, 1000),
            'role' => 'cover',
        ])->assertStatus(200);

        $image = Image::first();

        Storage::assertExists($image->small);
        list($width, $height, $type, $attr) = getimagesize(Storage::path($image->small));
        $this->assertEquals([50, 42], [$width, $height]);

        Storage::assertExists($image->medium);
        list($width, $height, $type, $attr) = getimagesize(Storage::path($image->medium));
        $this->assertEquals([120, 100], [$width, $height]);

        Storage::assertExists($image->large);
        list($width, $height, $type, $attr) = getimagesize(Storage::path($image->large));
        $this->assertEquals([300, 250], [$width, $height]);

        Storage::assertExists($image->super_big);
        list($width, $height, $type, $attr) = getimagesize(Storage::path($image->super_big));
        $this->assertEquals([600, 500], [$width, $height]);
    }
}
