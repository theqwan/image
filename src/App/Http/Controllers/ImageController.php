<?php

namespace Qwantum\Image\App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Qwantum\Image\App\Http\Requests\ImageRequest;
use Qwantum\Image\Services\ImageService;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;

class ImageController extends Controller
{
    /**
     * @var ImageService
     */
    protected $imageService;

    public function __construct()
    {
        $this->imageService = new ImageService;
    }

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
            $image = $this->imageService->saveImage($file, $folder, $request->input('role'), $request->input('location'), $request->input('manual_order'));

            return response()->json(array_merge($image->toArray(), ['_token' => csrf_token()]), 200);
        }

        throw new UploadException('上傳檔案失敗。');
    }
}
