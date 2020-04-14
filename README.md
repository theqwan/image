# Qwantum/Image

該套件提供圖片上傳與製作縮圖的功能。使用 ImageMagick 和 intervention/image 來製作縮圖。

## Composer
```
composer install qwantum/image
```

## Setups

1. 設定 routes：
```php
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
```javascript
$.post('{{ route('image.upload', $folder) }}', {
  '_token': $('input[name="_token"]').val(),
  'upload': $('input[name="upload"]').val(),
  // 'role': $('input[name="cover"]').val(),
  // 'location': $('input[name="location"]').val(),
  // 'manual_order': $('input[name="manual_order"]').val(),
});
```

2. 跟隨 `<form>` 表單上傳的圖片，可以使用 `Qwantum\Image\Facades\Image::saveImageByUploadedFile()` 將圖片存入資料庫。
```php
use Qwantum\Image\Facades\Image;

public function store(Request $request)
{
    Image::saveImageByUploadedFile($request->file('image'), 'folder');
}
```

## 獲取圖片路徑的方式

- 原始上傳圖片路徑： `$file->original_path_with_filename`
- 預設大小圖片路徑： `$file->path_with_filename`
- thumbnail圖片路徑： `$file->{$thumbnail_name}`

## 獲取圖片網址

網址的 Domain 可在 `config/qwantum.image.php` 中的 `storage_domain` 設定。

獲取原始大小圖的網址
```php
$image->url;
```

獲取 thumbnails 的網址
```php
$image->{$thumbnail_name}_url;
or
Qwantum\Image\Facades\ImageStorageUrl::to($image->{$thumbnail_name});
```

> 若要強制使用 https 可在 AppServiceProvider 的 boot() 新增
> `Qwantum\Image\Facades\ImageStorageUrl::forceScheme('https')`

## 各別控制

若有某個 Model 不想產生縮圖 或 縮圖需要特別設定大小 或 不要調整最大寬度，可以跟隨以下步驟來設定：

1. 設定資料夾與 Model 的對應表。

```php
// config/qwantum.image.php
'folder_to_model' => [
    'users' => \App\User::class,
],
```

2. 設定參數在 Model 中。

```php
class User extends Model
{
    use Imageable;

    public static $is_need_generate_thumbnails = true;

    public static $generate_thumbnail_settings = [
        'small' => [100, 100],
        'large' => [500, 500],
    ];

    public static $is_need_resize_to_max_width = false;
}
```
