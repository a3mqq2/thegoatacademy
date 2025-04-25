<?php
// app/Console/Commands/SendDailyCoursesImage.php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\User;
use App\Models\CourseType;
use App\Services\WaapiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class SendDailyCoursesImage extends Command
{
    protected $signature   = 'courses:send-daily-image';
    protected $description = 'Generate today\'s A4 course schedule, convert to image, send via WhatsApp';

    public function handle(WaapiService $waapi): int
    {
        $today   = Carbon::today()->addDays(2);
        $courses = Course::with(['exams','courseType'])
            ->where(fn($q)=>$q
                ->whereDate('pre_test_date',$today)
                ->orWhereDate('mid_exam_date',$today)
                ->orWhereDate('final_exam_date',$today)
            )->get();

        /* ---- render Blade ---- */
        $html = View::make('exam_officer.courses.print', [
            'courses'     => $courses,
            'instructors' => User::role('Instructor')->get(),
            'examiners'   => User::role('Examiner')->get(),
            'courseTypes' => CourseType::all(),
            'afterOne'    => $today->copy()->addDay(),
            'today'       => $today,
        ])->render();

        /* ---- HTML → PDF (A4) ---- */
        $pdfBin = Pdf::loadHTML($html)
                     ->setPaper('A4','portrait')
                     ->setOptions([
                         'dpi'                  => 150,
                         'isRemoteEnabled'      => true,
                         'isHtml5ParserEnabled' => true,
                     ])->output();

        $tmpPdf = storage_path('app/daily_courses.pdf');
        file_put_contents($tmpPdf, $pdfBin);

        /* ---- PDF → JPG ---- */
        $im = new \Imagick();
        $im->setResolution(300,300);
        $im->readImage($tmpPdf.'[0]'); // صفحة كاملة
        $im->setImageBackgroundColor('white');
        $im = $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);

        $im->sharpenImage(0,1);
        $im->setImageFormat('jpg');
        $im->setImageCompressionQuality(90);

        // تصغير متناسب بعرض 1080px (الارتفاع يُحسب تلقائياً)
        $im->resizeImage(1080, 0, \Imagick::FILTER_LANCZOS, 1);

        $fileName  = 'prints/daily_courses_'.now()->format('Ymd_His').'.jpg';
        Storage::disk('public')->put($fileName, $im);
        $publicUrl = asset('storage/'.$fileName);

        unlink($tmpPdf);                            // clean temp file

        /* ---- send via WhatsApp ---- */
        $caption = '📋 جدول الامتحانات – '.$today->format('Y-m-d');
        $waapi->sendImage(env('EXAM_MANAGER_CHATID'), $publicUrl, $caption);

        $this->info('Daily course image sent successfully.');
        return self::SUCCESS;
    }
}
