<?php

// namespace VGirol\JsonApi\Tests\Tools\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhotoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photo', function (Blueprint $table) {
            $table->increments('PHOTO_ID');
            $table->unsignedInteger('AUTHOR_ID')->nullable();
            $table->string('PHOTO_TITLE', 255)->unique();
            $table->unsignedInteger('PHOTO_SIZE');
            $table->dateTime('PHOTO_DATE')->nullable();

            $table->foreign('AUTHOR_ID', 'photo_author_foreign')->references('AUTHOR_ID')->on('author')
                ->onDelete('restrict')
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
        Schema::dropIfExists('photo');
    }
}
