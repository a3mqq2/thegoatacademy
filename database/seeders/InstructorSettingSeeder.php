<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class InstructorSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'Allowed instructor absence count per course', 'value' => '3'],
            ['key' => 'Instructor absence warning threshold per course', 'value' => '2'],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::create($setting);
        }
    }
}
