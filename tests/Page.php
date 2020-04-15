<?php

namespace Qwantum\Image\Tests;

use Illuminate\Database\Eloquent\Model;
use Qwantum\Image\Imageable;

class Page extends Model
{
    use Imageable;

    protected $table = 'pages';

    public static $is_need_generate_thumbnails = false;

    public static $generate_thumbnail_settings = [
        'big' => [1000, 1000],
    ];

    public static $is_need_resize_to_max_width = false;

    protected $fillable = [
        'title',
    ];
}
