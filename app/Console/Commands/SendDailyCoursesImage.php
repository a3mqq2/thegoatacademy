<?php

namespace App\Console\Commands;

use App\Models\Course;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use App\Services\WaapiService;

class SendDailyCoursesImage extends Command
{
    protected $signature   = 'courses:send-daily-image';
    protected $description = 'Generate today’s course schedule (A4) and send it as an image via WhatsApp';

    public function handle(WaapiService $waapi): int
    {
        $today = Carbon::today();          // عدّل التاريخ كما تريد

        /* دور على الكورسات */
        $courses = Course::with(['exams','courseType'])
            ->where(fn($q)=>$q
                ->whereDate('pre_test_date',   $today)
                ->orWhereDate('mid_exam_date', $today)
                ->orWhereDate('final_exam_date',$today)
            )->get();

        if ($courses->isEmpty()) {
            $this->info('No courses for '.$today->toDateString().'; nothing sent.');
            return self::SUCCESS;
        }

        /* شعار Base64 */
        $logoB64 = 'data:image/svg+xml;base64,'.
                   base64_encode(file_get_contents(public_path('images/logo.svg')));

        /* render Blade */
        $html = View::make('exam_officer.courses.print', [
            'courses' => $courses,
            'today'   => $today,
            'logo'    => $logoB64,
        ])->render();

        /* HTML → PDF */
        $pdfBin = Pdf::loadHTML($html)
                     ->setPaper('A4','portrait')
                     ->setOptions([
                         'dpi'               => 150,
                         'isRemoteEnabled'   => true,
                         'isHtml5ParserEnabled' => true,
                     ])->output();

        $tmpPdf = storage_path('app/daily_courses.pdf');
        file_put_contents($tmpPdf, $pdfBin);

        /* PDF → JPG */
        $im = new \Imagick();
        $im->setResolution(300,300);
        $im->readImage($tmpPdf.'[0]');
        $im = $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        $im->setImageFormat('jpg');
        $im->setImageCompressionQuality(92);
        $im->resizeImage(2000, 0, \Imagick::FILTER_LANCZOS, 1); // أوسع وأوضح

        $file   = 'prints/daily_courses_'.now()->format('Ymd_His').'.jpg';
        Storage::disk('public')->put($file, $im);
        $url    = asset('storage/'.$file);

        unlink($tmpPdf);

        /* إرسال */
        // $waapi->sendImage(env('EXAM_MANAGER_CHATID'), $url,
        //                   '📋 جدول الامتحانات – '.$today->format('Y-m-d'));

        $this->info('Daily course image generated: '.$url);
        return self::SUCCESS;
    }
}
