<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DoctorVisit;
use Faker\Factory as Faker;

class DoctorVisitSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        foreach (range(1, 10) as $index) {
            DoctorVisit::create([
                'hospital' => $faker->company,
                'type' => $faker->randomElement(['government', 'private']),
                'potential' => $faker->randomElement(['low', 'medium', 'high']),
                'status' => $faker->randomElement(['open', 'closed']),
                'chain' => $faker->companySuffix,
                'address' => $faker->address,
                'city' => $faker->city,
                'contact_person' => $faker->name,
                'contact_position' => $faker->jobTitle,
                'phone_number' => $faker->phoneNumber,
                'email' => $faker->email,
                'responsible' => $faker->name,
                'visits' => $faker->paragraph,
            ]);
        }
    }
}