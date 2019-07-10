<?php

namespace Qwantum\Image\Tests;

use Illuminate\Database\Eloquent\Model;
use Qwantum\Image\Imageable;

class Article extends Model
{
    use Imageable;

    protected $table = 'articles';

    protected $fillable = [
        'title',
    ];
}
