<?php

namespace Qwantum\Image;

use Illuminate\Database\Eloquent\Model;
use Qwantum\Image\Facades\ImageStorageUrl;

class Image extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'imageable_id',
        'imageable_type',
        'role',
        'filename',
        'original_filename',
        'mime_type',
        'extension',
        'path',
        'size',
        'location',
        'manual_order',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'size' => 'int',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'url',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function imageable()
    {
        return $this->morphTo();
    }

    /**
     * @return string
     */
    public function getUrlAttribute()
    {
        return ImageStorageUrl::to($this->getPathWithFilenameAttribute());
    }

    /**
     * @return string
     */
    public function getPathWithFilenameAttribute()
    {
        return preg_replace("/[\/]{2}/", '/', "{$this->path}/{$this->filename}.{$this->extension}");
    }

    /**
     * @return string
     */
    public function getOriginalPathWithFilenameAttribute()
    {
        if (config('qwantum.image.keep_original')) {
            return preg_replace("/[\/]{2}/", '/', "{$this->path}/original/{$this->filename}.{$this->extension}");
        }

        return $this->getPathWithFilenameAttribute();
    }

    /**
     * This is not EloquentModel accessor function.
     * Use `$model->{$thumbnail_name}` to get path.
     *
     * @param string $thumbnail_name
     * @return string
     */
    protected function getThumbnailPathWithFilename($thumbnail_name)
    {
        return preg_replace("/[\/]{2}/", '/', "{$this->path}/{$this->filename}_{$thumbnail_name}.{$this->extension}");
    }

    public function __get($key)
    {
        // get thumbnails path
        if (array_key_exists($key, config('qwantum.image.thumbnails'))) {
            return $this->getThumbnailPathWithFilename($key);
        }

        return parent::__get($key);
    }
}
