<?php

namespace Qwantum\Image\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class ImageServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function images_table_is_migrated()
    {
        $this->assertTrue(Schema::hasTable('images'));
    }
}
