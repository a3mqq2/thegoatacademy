<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GroupTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // group types private -group - TRI
        $groupTypes = [
            ['name' => 'Private', 'student_capacity' => 1, 'lesson_duration' => 60],
            ['name' => 'Group', 'student_capacity' => 10, 'lesson_duration' => 60],
            ['name' => 'TRI', 'student_capacity' => 3, 'lesson_duration' => 60],
        ];

        foreach ($groupTypes as $groupType) {
            \App\Models\GroupType::firstOrCreate($groupType);
        }
    }
}
