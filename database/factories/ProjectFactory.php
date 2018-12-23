<?php

use Faker\Generator as Faker;

$factory->define(\App\Project::class, function (Faker $faker) {
    $state = $faker->randomElement(['OPENED', 'IDEA', 'FUNDING-REQUIRED', 'WORK-IN-PROGRESS', 'COMPLETED']);
    $status = $faker->randomElement(['opened', 'closed', 'locked', 'merged']);
    return [
        'title' => $faker->sentence(),
        'payment_id' => $faker->sha256,
        'target_amount' => $faker->randomFloat(2, 0, 2000),
        'state' => $state,
        'merge_request_id' => $faker->randomNumber(6),
        'gitlab_username' => $faker->userName,
        'gitlab_url' => $faker->url,
        'gitlab_state' => $status,
        'gitlab_created_at' => $faker->dateTimeThisYear,
        'created_at' => $faker->dateTimeThisYear,
        'updated_at' => $faker->dateTimeThisYear,
    ];
});
