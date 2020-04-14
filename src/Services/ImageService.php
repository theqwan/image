<?php

namespace Qwantum\Image\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Qwantum\Image\Exceptions\CompressException;
use Qwantum\Image\Exceptions\ResizeException;
use Qwantum\Image\Image;

class ImageService
{
    /**
     * Namespace of model.
     *
     * @var string|null
     */
    protected $imageable = null;

    /**
     * @param string $folder
     * @return void
     */
    protected function init($folder)
    {
        if (array_key_exists($folder, config('qwantum.image.folder_to_model'))) {
            $this->imageable = config("qwantum.image.folder_to_model.{$folder}");
        }
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param string $folder
     * @param string|null $role
     * @param string|null $location
     * @param int|null $manual_order
     * @return \Qwantum\Image\Image|\Illuminate\Database\Eloquent\Model
     */
    public function saveImage(UploadedFile $uploadedFile, $folder, $role = null, $location = null, $manual_order = null)
    {
        $this->init($folder);

        $filename = pathinfo($uploadedFile->hashName(), PATHINFO_FILENAME);
        $original_filename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $mime_type = $uploadedFile->getMimeType();
        $extension = $uploadedFile->getClientOriginalExtension();
        $path = $this->moveTo($folder);
        $size = $uploadedFile->getSize();
        list($width, $height, $type, $attr) = getimagesize($uploadedFile->getRealPath());

        Storage::putFileAs($this->moveTo($folder), $uploadedFile, "{$filename}.{$extension}");

        $uploadedFile_path = str_replace(' ', '\ ', Storage::path($this->moveTo($folder, "{$filename}.{$extension}")));

        // resize
        if ($width > config('qwantum.image.max_width')) {
            $this->resizeToMaxWidth($uploadedFile_path);

            $size = Storage::size($this->moveTo($folder, "{$filename}.{$extension}"));
        }

        // compress
        $this->compress($uploadedFile_path);

        // create thumbnail image
        $this->generateThumbnails($folder, $filename, $extension);

        // keep original image
        $this->keepOriginal($uploadedFile, $folder, $filename, $extension);

        $image = Image::query()->create(compact('role', 'filename', 'original_filename', 'mime_type', 'extension', 'path', 'size', 'location', 'manual_order'));

        return $image;
    }

    /**
     * @param string $folder
     * @param string|null $filename
     * @return string
     */
    protected function moveTo($folder, $filename = null)
    {
        $base_path = date('Y') . '/' . date('m');

        return preg_replace("/[\/]{2}/", '/', "{$base_path}/{$folder}/{$filename}");
    }

    /**
     * @param string $uploadedFile_path
     * @return void
     * @throws ResizeException
     */
    protected function resizeToMaxWidth($uploadedFile_path)
    {
        if ($this->imageable && !$this->imageable::isNeedResizeToMaxWidth()) {
            return;
        }

        $resize_command = config('qwantum.image.imagemagick_path') . str_replace([':path', ':max_width'], [$uploadedFile_path, config('qwantum.image.max_width')], config('qwantum.image.resize_max_width_command'));
        exec($resize_command, $exce_output, $exec_result);

        if ($exec_result) {
            throw new ResizeException('resize error');
        }
    }

    /**
     * @param string $uploadedFile_path
     * @return void
     * @throws CompressException
     */
    protected function compress($uploadedFile_path)
    {
        $compress_command = config('qwantum.image.imagemagick_path') . str_replace(':path', $uploadedFile_path, config('qwantum.image.compress_command'));
        exec($compress_command, $exce_output, $exec_result);

        if ($exec_result) {
            throw new CompressException('compress error');
        }
    }

    /**
     * @param $folder
     * @param $filename
     * @param string $extension
     * @return void
     */
    protected function generateThumbnails($folder, $filename, string $extension)
    {
        if ($this->imageable && !$this->imageable::isNeedGenerateThumbnails()) {
            return;
        }

        $thumbnail_settings = $this->imageable ? $this->imageable::getGenerateThumbnailSettings() : config('qwantum.image.thumbnails');

        foreach ($thumbnail_settings as $thumbnail_name => $resize_setting) {
            list($width, $height) = $resize_setting;

            $thumbnail_image = \Intervention\Image\Facades\Image::make(Storage::path($this->moveTo($folder, "{$filename}.{$extension}")))
                ->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->stream();

            Storage::put($this->moveTo($folder, "{$filename}_{$thumbnail_name}.{$extension}"), $thumbnail_image);
        }
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param $folder
     * @param $filename
     * @param string $extension
     * @return void
     */
    protected function keepOriginal(UploadedFile $uploadedFile, $folder, $filename, string $extension)
    {
        if (config('qwantum.image.keep_original')) {
            Storage::putFileAs($this->moveTo("{$folder}/original"), $uploadedFile, "{$filename}.{$extension}");
        }
    }
}
