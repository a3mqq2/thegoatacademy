<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CourseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // columns : name, status (active, inactive), duration in weeks , attach skills also
        $courseTypes = [
            ['name' => 'General English', 'duration' => 12],
            ['name' => 'IELTS', 'duration' => 12],
            ['name' => 'TOEFL', 'duration' => 12],
            ['name' => 'TOEIC', 'duration' => 12],
            ['name' => 'Business English', 'duration' => 12],
            ['name' => 'Conversation', 'duration' => 12],
            ['name' => 'Grammar', 'duration' => 12],
            ['name' => 'Vocabulary', 'duration' => 12],
            ['name' => 'Pronunciation', 'duration' => 12],
            ['name' => 'Writing', 'duration' => 12],
            ['name' => 'Reading', 'duration' => 12],
            ['name' => 'Listening', 'duration' => 12],
            ['name' => 'Speaking', 'duration' => 12],
        ];

        foreach ($courseTypes as $courseType) {
            \App\Models\CourseType::firstOrCreate($courseType);

            // attach skills to course type randomly
            $skills = Skill::inRandomOrder()->limit(rand(1, 3))->get();
            $courseType = \App\Models\CourseType::where('name', $courseType['name'])->first();
            $courseType->skills()->attach($skills);
        }

    }
}
