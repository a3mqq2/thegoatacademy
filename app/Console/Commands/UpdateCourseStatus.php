<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateCourseStatus extends Command
{
    protected $signature = 'courses:update-status';
    protected $description = 'Update course status (upcoming / ongoing) based on start_date';

    public function handle(): int
    {
        DB::transaction(function () {
            $today = Carbon::today();

            Course::whereDate('start_date', '<=', $today)
                ->where('status', '!=', 'ongoing')
                ->update(['status' => 'ongoing']);

            Course::whereDate('start_date', '>', $today)
                ->where('status', '!=', 'upcoming')
                ->update(['status' => 'upcoming']);
        });

        return self::SUCCESS;
    }
}
