<?php

// namespace VGirol\JsonApi\Tests\Tools\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comment', function (Blueprint $table) {
            $table->increments('COMMENT_ID');
            $table->unsignedInteger('AUTHOR_ID');
            $table->unsignedInteger('PHOTO_ID');
            $table->text('COMMENT_TEXT');
            $table->dateTime('COMMENT_DATE');

            $table->foreign('AUTHOR_ID', 'comment_author_foreign')->references('AUTHOR_ID')->on('author')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('PHOTO_ID', 'comment_photo_foreign')->references('PHOTO_ID')->on('photo')
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
        Schema::dropIfExists('comment');
    }
}
