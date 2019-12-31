<?php

namespace Qwantum\Image\App\Http\Controllers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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

        if ($request->input('width') != 0 && $request->input('height') != 0) {
            $dimensions = Rule::dimensions();
            $dimensions = $request->input('width') ? $dimensions->width($request->input('width')) : $dimensions;
            $dimensions = $request->input('height') ? $dimensions->height($request->input('height')) : $dimensions;

            $validator = Validator::make($request->all(), [
                'upload' => [
                    $dimensions,
                ],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Upload Failed. Image resolution must be '.$request->input('width', '?').'px x '.$request->input('height', '?').'px.',
                ], 422);
            }
        }

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
    public function saveImageByUploadedFile(UploadedFile $uploadedFile, $folder, $role = null, $location = null, $manual_order = null)
    {
        if (Str::is('image/*', $uploadedFile->getMimeType())) {
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
        $base_path = date('Y').'/'.date('m');

        return preg_replace("/[\/]{2}/", '/', "{$base_path}/{$folder}/{$filename}");
    }

    /**
     * @param string $uploadedFile_path
     * @return void
     * @throws ResizeException
     */
    protected function resizeToMaxWidth($uploadedFile_path)
    {
        $resize_command = config('qwantum.image.imagemagick_path').str_replace([':path', ':max_width'], [$uploadedFile_path, config('qwantum.image.max_width')], config('qwantum.image.resize_max_width_command'));
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
        foreach (config('qwantum.image.thumbnails') as $thumbnail_name => $resize_setting) {
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
