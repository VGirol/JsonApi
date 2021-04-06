<?php

use Faker\Generator as Faker;

if (!isset($factory)) {
    return;
}

$factory->define(VGirol\JsonApi\Tests\Tools\Models\Comment::class, function (Faker $faker) {
    return [
        'COMMENT_ID' => $faker->unique()->randomNumber(3),
        'COMMENT_TEXT' => $faker->text(255),
        'COMMENT_DATE' => $faker->dateTime()->format('Y-m-d H:i:s'),
    ];
});
