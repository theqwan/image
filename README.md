# Qwantum/Image

該套件提供圖片上傳與製作縮圖的功能。使用 ImageMagick 和 intervention/image 來製作縮圖。

## Composer
```
composer install qwantum/image
```

## Setups

1. 設定 routes：
```
Qwantum\Image\Facades\Image::route();
```

2. 在 Model 中使用 `Imageable`
```php
<?php

use Qwantum\Image\Imageable;

class Article extends Illuminate\Database\Eloquent\Model
{
    use Imageable;
}
```

3. Publish 設定檔
```
php artisan vendor:publish --tag=Image-config
```

## Upload Image

儲存圖片有兩種方式

1. 透過AJAX上傳：AJAX的POST網址是 `route('image.upload', $folder)` (這是基本的name，注意自定義的prefix)，須將圖片的input的name設為upload
```
$.post('{{ route('image.upload', $folder) }}', {
  '_token': $('input[name="_token"]').val(),
  'upload': $('input[name="upload"]').val(),
  // 'role': $('input[name="cover"]').val(),
  // 'location': $('input[name="location"]').val(),
  // 'manual_order': $('input[name="manual_order"]').val(),
});
```

2. ImageController 中有 `saveImageByUploadedFile()` 方法可以使用，第一個參數得為 `Illuminate\Http\UploadedFile`

## 獲取圖片路徑的方式

- 原始上傳圖片路徑： `$file->original_path_with_filename`
- 預設大小圖片路徑： `$file->path_with_filename`
- thumbnail圖片路徑： `$file->{$thumbnail_name}`
