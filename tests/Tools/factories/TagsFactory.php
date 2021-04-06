<?php

use Faker\Generator as Faker;

if (!isset($factory)) {
    return;
}

$factory->define(VGirol\JsonApi\Tests\Tools\Models\Tags::class, function (Faker $faker) {
    return [
        'TAGS_ID' => $faker->unique()->randomNumber(3),
        'TAGS_NAME' => $faker->word(),
    ];
});
