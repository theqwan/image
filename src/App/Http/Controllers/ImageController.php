<?php

namespace Qwantum\Image\App\Http\Controllers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Qwantum\Image\App\Http\Requests\ImageRequest;
use Qwantum\Image\Exceptions\CompressException;
use Qwantum\Image\Exceptions\ResizeException;
use Qwantum\Image\Exceptions\TypeException;
use Qwantum\Image\Image;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;

class ImageController extends Controller
{
    /**
     * @param ImageRequest $request
     * @param string $folder
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(ImageRequest $request, $folder)
    {
        $file = $request->file('upload');

        if ($file->isValid()) {
            $image = $this->saveImage($file, $folder, $request->input('role'), $request->input('location'), $request->input('manual_order'));

            return response()->json(array_merge($image->toArray(), ['_token' => csrf_token()]), 200);
        }

        throw new UploadException('上傳檔案失敗。');
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param string $folder
     * @param string|null $role
     * @param string|null $location
     * @param int|null $manual_order
     * @return \Qwantum\Image\Image|\Illuminate\Database\Eloquent\Model
     */
    public function uploadFromFormPost(UploadedFile $uploadedFile, $folder, $role = null, $location = null, $manual_order = null)
    {
        if (Str::is('image/*', $uploadedFile->getClientMimeType())) {
            return $this->saveImage($uploadedFile, $folder, $role, $location, $manual_order);
        }

        throw new TypeException('上傳檔案的類別不是圖片。');
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param string $folder
     * @param string|null $role
     * @param string|null $location
     * @param int|null $manual_order
     * @return \Qwantum\Image\Image|\Illuminate\Database\Eloquent\Model
     */
    protected function saveImage(UploadedFile $uploadedFile, $folder, $role = null, $location = null, $manual_order = null)
    {
        $filename = pathinfo($uploadedFile->hashName(), PATHINFO_FILENAME);
        $original_filename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $mime_type = $uploadedFile->getClientMimeType();
        $extension = $uploadedFile->getClientOriginalExtension();
        $path = $this->moveTo($folder);
        $size = $uploadedFile->getSize();
        list($width, $height, $type, $attr) = getimagesize($uploadedFile->getRealPath());

        // compress
        Storage::putFileAs($this->moveTo($folder), $uploadedFile, "{$filename}.{$extension}");
        $uploadedFile_path = str_replace(' ', '\ ', Storage::path($this->moveTo($folder, "{$filename}.{$extension}")));

        if ($width > config('qwantum.image.max_width')) {
            $resize_command = config('qwantum.image.imagemagick_path').str_replace([':path', ':max_width'], [$uploadedFile_path, config('qwantum.image.max_width')], config('qwantum.image.resize_max_width_command'));
            exec($resize_command, $exce_output, $exec_result);
            if ($exec_result) {
                throw new ResizeException('resize error');
            }

            $size = Storage::size($this->moveTo($folder, "{$filename}.{$extension}"));
        }

        $compress_command = config('qwantum.image.imagemagick_path').str_replace(':path', $uploadedFile_path, config('qwantum.image.compress_command'));
        exec($compress_command, $exce_output, $exec_result);
        if ($exec_result) {
            throw new CompressException('compress error');
        }

        // create thumbnail image
        foreach (config('qwantum.image.thumbnails') as $thumbnail_name => $resize_setting) {
            list($width, $height) = $resize_setting;

            $thumbnail_image = \Intervention\Image\Facades\Image::make(Storage::path($this->moveTo($folder, "{$filename}.{$extension}")))->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->stream();

            Storage::put($this->moveTo($folder, "{$filename}_{$thumbnail_name}.{$extension}"), $thumbnail_image);
        }

        // keep original image
        if (config('qwantum.image.keep_original')) {
            Storage::putFileAs($this->moveTo("{$folder}/original"), $uploadedFile, "{$filename}.{$extension}");
        }

        $image = Image::query()->create([
            'role' => $role,
            'filename' => $filename,
            'original_filename' => $original_filename,
            'mime_type' => $mime_type,
            'extension' => $extension,
            'path' => $path,
            'size' => $size,
            'location' => $location,
            'manual_order' => $manual_order,
        ]);

        return $image;
    }

    /**
     * @param string $folder
     * @param string|null $filename
     * @return string
     */
    protected function moveTo($folder, $filename = null)
    {
        $base_path = date('Y').'/'.date('m');

        return preg_replace("/[\/]{2}/", '/', "{$base_path}/{$folder}/{$filename}");
    }
}
