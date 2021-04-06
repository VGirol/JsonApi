<?php

// namespace VGirol\JsonApi\Tests\Tools\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePivotPhotoTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pivot_phototags', function (Blueprint $table) {
            $table->increments('PIVOT_ID');
            $table->unsignedInteger('PHOTO_ID');
            $table->unsignedInteger('TAGS_ID');
            $table->string('PIVOT_COMMENT', 100)->nullable();

            $table->unique(['PHOTO_ID', 'TAGS_ID']);

            $table->foreign('PHOTO_ID', 'pivot_phototags_photo_foreign')->references('PHOTO_ID')->on('photo')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('TAGS_ID', 'pivot_phototags_tags_foreign')->references('TAGS_ID')->on('tags')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pivot_phototags');
    }
}
