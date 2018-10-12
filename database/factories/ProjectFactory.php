<?php

use Faker\Generator as Faker;

$factory->define(\App\Project::class, function (Faker $faker) {
    return [
        'payment_id' => $faker->sha256,
        'target_amount' => $faker->randomNumber(),
        'status' => $faker->randomElement(['new', 'open', 'funded']),
        'created_at' => $faker->dateTime,
        'updated_at' => $faker->dateTime,
    ];
});
