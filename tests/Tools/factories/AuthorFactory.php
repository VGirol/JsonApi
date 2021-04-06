<?php

use Faker\Generator as Faker;

if (!isset($factory)) {
    return;
}

$factory->define(VGirol\JsonApi\Tests\Tools\Models\Author::class, function (Faker $faker) {
    return [
        'AUTHOR_ID' => $faker->unique()->randomNumber(3),
        'AUTHOR_NAME' => $faker->name(),
    ];
});
