<?php

use Faker\Generator as Faker;

$factory->define(\App\Project::class, function (Faker $faker) {
    $state = $faker->randomElement(['OPENED', 'IDEA', 'FUNDING-REQUIRED', 'WORK-IN-PROGRESS', 'COMPLETED']);
    $status = $faker->randomElement(['opened', 'closed', 'locked', 'merged']);
    return [
        'title' => $faker->sentence(),
        'subaddr_index' => $faker->randomNumber(),
        'address' => $faker->sha256,
        'address_uri' => "monero:{$faker->sha256}",
        'qr_code' => $faker->file(),
        'target_amount' => $faker->randomFloat(2, 0, 2000),
        'raised_amount' => $faker->randomFloat(2, 0, 2000),
        'state' => $state,
        'author' => $faker->userName,
        'gitlab_url' => $faker->url,
        'created_at' => $faker->dateTimeThisYear,
        'updated_at' => $faker->dateTimeThisYear,
    ];
});
