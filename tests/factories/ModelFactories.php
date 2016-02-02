<?php

//////////
// User //
//////////

$factory(Timegridio\Tests\Models\User::class, function (Faker\Generator $faker) {
    return [
        'username' => $faker->firstName,
        'name'     => $faker->firstName,
        'email'    => $faker->safeEmail,
    ];
});

/////////////
// Contact //
/////////////

$factory(Timegridio\Concierge\Models\Contact::class, function (Faker\Generator $faker) {
    return [
        'firstname'      => $faker->firstName,
        'lastname'       => $faker->lastName,
        'nin'            => $faker->numberBetween(25000000, 50000000),
        'email'          => $faker->safeEmail,
        'birthdate'      => \Carbon\Carbon::now()->subYears(30)->format('Y-m-d'),
        'mobile'         => null,
        'mobile_country' => null,
        'gender'         => $faker->randomElement(['M', 'F']),
        'occupation'     => $faker->title,
        'martial_status' => null,
        'postal_address' => $faker->address,
    ];
});

//////////////
// Business //
//////////////

$factory(Timegridio\Concierge\Models\Business::class, [
    'name'            => $faker->sentence(3),
    'description'     => $faker->paragraph,
    'timezone'        => $faker->timezone,
    'postal_address'  => $faker->address,
    'phone'           => null,
    'social_facebook' => 'https://www.facebook.com/example?fref=ts',
    'strategy'        => 'dateslot',
    'plan'            => 'free',
    'category_id'     => $faker->randomElement([1, 2, 3]),
]);

//////////////////
// Service Type //
//////////////////

$factory(Timegridio\Concierge\Models\ServiceType::class, function (Faker\Generator $faker) {
    return [
        'business_id' => 'factory:Timegridio\Concierge\Models\Business',
        'name'        => $faker->sentence(3),
        'description' => $faker->paragraph,
    ];
});

/////////////
// Service //
/////////////

$factory(Timegridio\Concierge\Models\Service::class, function (Faker\Generator $faker) {
    return [
        'business_id'   => 'factory:Timegridio\Concierge\Models\Business',
        'name'          => $faker->sentence(2),
        'description'   => $faker->paragraph,
        'prerequisites' => $faker->paragraph,
        'duration'      => $faker->randomElement([15, 30, 60, 120]),
    ];
});

/////////////
// Vacancy //
/////////////

$factory(Timegridio\Concierge\Models\Vacancy::class, function (Faker\Generator $faker) {
    $date = $faker->dateTimeBetween('today', 'today +7 days')->format('Y-m-d');

    return [
        'business_id' => 'factory:Timegridio\Concierge\Models\Business',
        'service_id'  => 'factory:Timegridio\Concierge\Models\Service',
        'date'        => date('Y-m-d', strtotime($date)),
        'start_at'    => date('Y-m-d 00:00:00', strtotime($date)),
        'finish_at'   => date('Y-m-d 23:00:00', strtotime($date)),
        'capacity'    => 1,
    ];
});

/////////////////
// Appointment //
/////////////////

$factory(Timegridio\Concierge\Models\Appointment::class, function (Faker\Generator $faker) {
    return [
        'business_id' => 'factory:Timegridio\Concierge\Models\Business',
        'contact_id'  => 'factory:Timegridio\Concierge\Models\Contact',
        'service_id'  => 'factory:Timegridio\Concierge\Models\Service',
        'vacancy_id'  => 'factory:Timegridio\Concierge\Models\Vacancy',
        'status'      => $faker->randomElement(['R', 'C', 'A', 'S']),
        'start_at'    => Carbon\Carbon::parse(date('Y-m-d 08:00:00', strtotime('today +2 days'))),
        'duration'    => $faker->randomElement([15, 30, 60, 120]),
        'comments'    => $faker->sentence,
    ];
});

////////////
// Domain //
////////////

$factory(Timegridio\Concierge\Models\Domain::class, function ($faker) {
    return [
        'slug'     => str_slug($faker->name),
        'owner_id' => 'factory:Timegridio\Tests\Models\User',
    ];
});
