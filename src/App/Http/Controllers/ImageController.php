<?php

namespace Qwantum\Image\App\Http\Controllers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Qwantum\Image\App\Http\Requests\ImageRequest;
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
     * @param null $role
     * @param null $location
     * @param null $manual_order
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

        if (config('image.keep_original')) {
            Storage::putFileAs($this->moveTo("{$folder}/original"), $uploadedFile, "{$filename}.{$extension}");
        }

        Storage::putFileAs($path, $uploadedFile, "{$filename}.{$extension}");

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
     * @return string
     */
    protected function moveTo($folder)
    {
        $base_path = date('Y').'/'.date('m');

        return preg_replace("/[\/]{2}/", '/', "{$base_path}/{$folder}/");
    }
}
