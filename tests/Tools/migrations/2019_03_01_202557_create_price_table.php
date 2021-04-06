<?php

// namespace VGirol\JsonApi\Tests\Tools\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price', function (Blueprint $table) {
            $table->increments('PRICE_ID');
            $table->unsignedInteger('PHOTO_ID')->nullable();
            $table->decimal('PRICE_VALUE', 8, 2);

            $table->foreign('PHOTO_ID', 'price_photo_foreign')->references('PHOTO_ID')->on('photo')
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
        Schema::dropIfExists('price');
    }
}
