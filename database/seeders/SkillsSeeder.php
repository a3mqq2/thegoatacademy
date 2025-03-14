<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SkillsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = ['speaking', 'listening', 'reading', 'writing', 'grammar', 'vocabulary', 'pronunciation'];

        foreach ($skills as $skill) {
            \App\Models\Skill::firstOrCreate(['name' => $skill]);
        }
    }
}
