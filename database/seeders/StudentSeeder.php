<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use Faker\Factory as Faker;
use Carbon\Carbon;

class StudentSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $totalStudents = 100; // Adjust the number of students to generate

        foreach (range(1, $totalStudents) as $index) {
            Student::create([
                'name'       => $faker->name,
                'phone'      => $faker->unique()->phoneNumber,
                'created_at' => Carbon::now()->subDays(rand(0, 30)), // Random date in the past 30 days
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
