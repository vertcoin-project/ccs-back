<?php

use Faker\Generator as Faker;

$factory->define(\App\Project::class, function (Faker $faker) {
    $status = $faker->randomElement(['opened', 'closed', 'locked', 'merged']);
    return [
        'title' => $faker->sentence(),
        'payment_id' => $faker->sha256,
        'target_amount' => $faker->randomFloat(2, 0, 2000),
        'state' => $status,
        'merge_request_id' => $faker->randomNumber(6),
        'created_at' => $faker->dateTimeThisYear,
        'updated_at' => $faker->dateTimeThisYear,
    ];
});
