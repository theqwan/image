<?php

namespace Qwantum\Image;

trait Imageable
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    // public static $is_need_generate_thumbnails = true;

    /**
     * Check this imageable is need generate thumbnails.
     *
     * @return boolean
     */
    public static function isNeedGenerateThumbnails()
    {
        return (bool) (property_exists(self::class, 'is_need_generate_thumbnails') ? self::$is_need_generate_thumbnails : true);
    }

    // public static $generate_thumbnail_settings = [];

    /**
     * Get thumbnail setting.
     *
     * @return void
     */
    public static function getGenerateThumbnailSettings()
    {
        if (property_exists(self::class, 'generate_thumbnail_settings') && is_array(self::$generate_thumbnail_settings)) {
            return self::$generate_thumbnail_settings;
        }

        return config('qwantum.image.thumbnails');
    }

    // public static $is_need_resize_to_max_width = true;

    /**
     * Check this imageable is need resize to max width.
     *
     * @return boolean
     */
    public static function isNeedResizeToMaxWidth()
    {
        return (bool) (property_exists(self::class, 'is_need_resize_to_max_width') ? self::$is_need_resize_to_max_width : true);
    }
}
