<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\WaapiService;

class SendDailyCoursesImage extends Command
{
    protected $signature = 'courses:send-daily-image';
    protected $description = 'Generate the daily course schedule and send it as an image via WhatsApp';

    public function handle(WaapiService $waapi): int
    {
        $today = Carbon::today();

        $courses = Course::with(['exams', 'courseType'])
            ->where(fn($q) => $q
                ->whereDate('pre_test_date', $today)
                ->orWhereDate('mid_exam_date', $today)
                ->orWhereDate('final_exam_date', $today)
            )
            ->get();

        $instructors = User::role('Instructor')->get();
        $examiners   = User::role('Examiner')->get();
        $courseTypes = \App\Models\CourseType::all();
        $afterOne    = $today->copy()->addDay();

        $html = View::make('exam_officer.courses.print', compact(
            'courses', 'instructors', 'examiners', 'courseTypes', 'afterOne', 'today'
        ))->render();

        /* 1) HTML → PDF */
        $pdf = Pdf::loadHTML($html)
                  ->setPaper('A4', 'portrait')
                  ->setOptions([
                      'isRemoteEnabled'      => true,
                      'isHtml5ParserEnabled' => true,
                      'dpi'                  => 150,
                  ]);

        $tmpPdf = storage_path('app/daily_courses.pdf');
        file_put_contents($tmpPdf, $pdf->output());

        /* 2) PDF → Image */
        $img = new \Imagick();
        $img->setResolution(300, 300);
        $img->readImage($tmpPdf . '[0]');
        $img->setImageBackgroundColor('white');
        $img = $img->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        $img->setImageFormat('jpg');
        $img->setImageCompressionQuality(90);

        $fileName = 'prints/daily_courses_' . now()->format('Ymd_His') . '.jpg';
        Storage::disk('public')->put($fileName, $img);
        $url = asset('storage/' . $fileName);

        /* 3) Send via WhatsApp */
        $waapi->sendImage("218912922162@c.us", $url, '📋 جدول الامتحانات اليومي');

        unlink($tmpPdf);

        $this->info('Daily course image sent successfully.');
        return self::SUCCESS;
    }
}
