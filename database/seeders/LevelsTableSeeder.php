<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Level;

class LevelsTableSeeder extends Seeder
{
    public function run()
    {
        $levels = [
            'Beginner',
            'Elementary',
            'Pre-Intermediate',
            'Intermediate',
            'Upper-Intermediate',
            'Advanced',
            'Proficiency',
        ];

        foreach ($levels as $levelName) {
            Level::create([
                'name' => $levelName,
            ]);
        }
    }
}
