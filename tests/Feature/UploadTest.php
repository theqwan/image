<?php

namespace Qwantum\Image\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
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
    public function upload_type_validate_test()
    {
        $response = $this->json('POST', route('images.upload', 'test'), [
            'upload' => UploadedFile::fake()->create('document.pdf'),
        ])->assertStatus(422);

        $this->assertTrue($response->exception instanceof ValidationException);
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
            'filename_length' => pathinfo($fake_image->hashName(), PATHINFO_FILENAME),
            'original_filename' => pathinfo($fake_image->getClientOriginalName(), PATHINFO_FILENAME),
            'mime_type' => $fake_image->getClientMimeType(),
            'extension' => $fake_image->getClientOriginalExtension(),
            'path' => date('Y').'/'.date('m').'/test/',
            'size' => $fake_image->getSize(),
        ];

        $actual = [
            'id' => $image->id,
            'imageable_id' => $image->imageable_id,
            'imageable_type' => $image->imageable_type,
            'role' => $image->role,
            'filename_length' => $image->filename,
            'original_filename' => $image->original_filename,
            'mime_type' => $image->mime_type,
            'extension' => $image->extension,
            'path' => $image->path,
            'size' => $image->size,
        ];

        $this->assertEquals($expected, $actual);

        Storage::assertExists($image->path_with_filename);
    }

    /** @test */
    public function keep_original_image_test()
    {
        Config::set('image.keep_original', true);

        $response = $this->json('POST', route('images.upload', 'test'), [
            'upload' => $fake_image = UploadedFile::fake()->image('avatar.jpg'),
            'role' => 'cover',
        ])->assertStatus(200);

        $image = Image::first();

        Storage::assertExists($image->original_path_with_filename);
    }
}
