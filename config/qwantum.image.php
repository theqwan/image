<?php

return [

    /*
    | Keep the original image setting, the original image path is: `/yyyy/mm/{folder}/original/`.
    | Default setting is "false".
    */
    'keep_original' => false,

    /*
    | ImageMagick command PATH.
    */
    'imagemagick_path' => env('IMAGEMAGICK_PATH', '/usr/local/bin/'),

    /*
    | If the uploaded image width exceeds this setting, it will be resize to this width.
    */
    'max_width' => 1920,

    /*
    | ImageMagick resize command, use `str_replace([':path', ':max_width'], [$full_path, $max_width], $command)` to replace :path and :max_width.
    |
    | Default setting: "convert :path -resize 1920 -sharpen 0.25x0.25 -quality 100 :path"
    */
    'resize_max_width_command' => 'convert :path -auto-level -resize :max_width -sharpen 0.25x0.25 -quality 100 :path',

    /*
    | ImageMagick compress command, use `str_replace(':path', $full_path, $command)` to replace ':path'.
    |
    | Default setting: "mogrify -quality 85 -filter Triangle -define filter:support=2 -unsharp 0.25x0.25+8+0.065 -dither None -define jpeg:fancy-upsampling=off -define png:compression-filter=5 -define png:compression-level=9 -define png:compression-strategy=1 -define png:exclude-chunk=all -interlace none -colorspace sRGB :path"
    */
    'compress_command' => 'mogrify -quality 85 -filter Triangle -define filter:support=2 -unsharp 0.25x0.25+8+0.065 -dither None -define jpeg:fancy-upsampling=off -define png:compression-filter=5 -define png:compression-level=9 -define png:compression-strategy=1 -define png:exclude-chunk=all -interlace none -colorspace sRGB :path',

    /*
    |--------------------------------------------------------------------------
    | Thumbnail Setting
    |--------------------------------------------------------------------------
    | Resize the image, this setting is optional so can leave empty.
    | setting example: `'thumbnail_name' => [{width}, {height}],`, width and height can set null.
    */
    'thumbnails' => [
        'small' => [50, 50],
        'medium' => [150, 150],
        'large' => [300, 300],
    ],

];
