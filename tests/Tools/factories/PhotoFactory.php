<?php

use Faker\Generator as Faker;

if (!isset($factory)) {
    return;
}

$factory->define(VGirol\JsonApi\Tests\Tools\Models\Photo::class, function (Faker $faker) {
    return [
        'PHOTO_ID' => $faker->unique()->randomNumber(3),
        'PHOTO_TITLE' => $faker->unique()->text(255),
        'PHOTO_SIZE' => $faker->randomNumber(9),
        'PHOTO_DATE' => $faker->dateTime()->format('Y-m-d H:i:s')
    ];
});
