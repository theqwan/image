<?php

return [

    /*
    | Keep the original image setting, the original image path is: `/yyyy/mm/{folder}/original/`.
    | Default setting is "false".
    */
    'keep_original' => env('IMAGE_KEEP_ORIGINAL', false),

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
