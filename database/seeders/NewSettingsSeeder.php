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
                'key' => 'Alter Student Absent For Days',
                'value' => 3,
            ],
            [
                'key' => 'Alter Student Missing Homework For Days',
                'value' => 3,
            ],
            [
                'key' => 'Stop Student Absent For Days',
                'value' => 6,
            ],
            [
                'key' => 'Stop Student Missing Homework For Days',
                'value' => 6,
            ],
            [
                'key' => 'Instructors Can Update Attendance Before Hours',
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
