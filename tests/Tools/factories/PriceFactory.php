<?php

use Faker\Generator as Faker;

if (!isset($factory)) {
    return;
}

$factory->define(VGirol\JsonApi\Tests\Tools\Models\Price::class, function (Faker $faker) {
    return [
        'PRICE_ID' => $faker->unique()->randomNumber(3),
        'PRICE_VALUE' => $faker->randomFloat(2, 0, 999999),
    ];
});
