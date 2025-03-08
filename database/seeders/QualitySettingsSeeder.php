<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QualitySetting;

class QualitySettingsSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            [
                'type' => 'progress_tests',
                'red_threshold' => 50,   // Scores below 50% are Red
                'yellow_threshold' => 70, // Scores from 50-70% are Yellow
                'green_threshold' => 85,  // Scores above 85% are Green
            ],
            [
                'type' => 'homeworks',
                'red_threshold' => 40,   // Submission below 40% are Red
                'yellow_threshold' => 75, // Submission from 40-75% are Yellow
                'green_threshold' => 90,  // Submission above 90% are Green
            ],
            [
                'type' => 'attendance',
                'red_threshold' => 60,   // Attendance below 60% are Red
                'yellow_threshold' => 80, // Attendance from 60-80% are Yellow
                'green_threshold' => 95,  // Attendance above 95% are Green
            ]
        ];

        foreach ($settings as $setting) {
            QualitySetting::updateOrCreate(
                ['type' => $setting['type']], 
                $setting
            );
        }
    }
}
