<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateCourseStatus extends Command
{
    protected $signature = 'courses:update-status';
    protected $description = 'Update course status based on dates';

    public function handle(): int
    {
        DB::transaction(function () {
            $today = Carbon::today();

            Course::whereDate('end_date', '<', $today)
                ->whereNotIn('status', ['completed', 'canceled', 'paused'])
                ->update(['status' => 'completed']);

            Course::whereDate('start_date', '<=', $today)
                ->whereNotIn('status', ['ongoing', 'completed', 'canceled', 'paused'])
                ->update(['status' => 'ongoing']);

            Course::whereDate('start_date', '>', $today)
                ->whereNotIn('status', ['upcoming', 'completed', 'canceled', 'paused'])
                ->update(['status' => 'upcoming']);
        });

        return self::SUCCESS;
    }
}
