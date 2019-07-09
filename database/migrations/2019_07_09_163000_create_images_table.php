<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->increments('id');
            $table->nullableMorphs('imageable');
            $table->string('role')->nullable();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('extension');
            $table->string('path');
            $table->unsignedInteger('size');
            $table->string('location')->nullable();
            $table->integer('manual_order')->nullable();
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('images');
    }
}
