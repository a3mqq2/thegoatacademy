<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NewSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'Student’s absence alert',
                'value' => 3,
            ],
            [
                'key' => 'Student’s missing homework’s alert',
                'value' => 3,
            ],
            [
                'key' => 'Dismissing the student because of absence',
                'value' => 6,
            ],
            [
                'key' => 'Dismissing the student because of not delivering the homework.',
                'value' => 6,
            ],
            [
                'key' => 'Updating the students’ Attendance after the class.',
                'value' => 12,
            ],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
