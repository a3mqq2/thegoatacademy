<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsForProgressTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key'   => 'Allow updating progress tests after class end time (hours)',
                'value' => 12,
            ],
            [
                'key'   => 'Notify instructor after update grace period (hours)',
                'value' => 3,
            ],
        ];

        // Insert or update each setting
        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
