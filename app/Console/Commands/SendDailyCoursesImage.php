<?php

namespace App\Console\Commands;

use App\Models\Course;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\View;
use App\Services\WaapiService;

class SendDailyCoursesImage extends Command
{
    protected $signature   = 'courses:send-daily-image';
    protected $description = 'Generate today’s course schedule (A4) and send it as an image via WhatsApp';

    public function handle(WaapiService $waapi): int
    {
        $today   = Carbon::today();
        $courses = Course::query()
                  ->whereDate('pre_test_date',   $today)
                  ->orWhereDate('mid_exam_date', $today)
                  ->orWhereDate('final_exam_date', $today)
                  ->get();

        $bgB64 = base64_encode(file_get_contents(public_path('images/cource.png')));

        $html  = View::make('exam_officer.courses.print-2', [
            'courses'=> $courses,
            'today'  => $today,
            'bgData' => $bgB64,
        ])->render();

        $pt   = 255.1;
        $pdf  = Pdf::loadHTML($html)
                   ->setPaper([0, 0, $pt, $pt])
                   ->setOptions([
                       'dpi' => 96,
                       'isRemoteEnabled' => true,
                       'isHtml5ParserEnabled' => true
                   ])
                   ->output();

        $tmpPath = storage_path('app/tmp_card.pdf');
        file_put_contents($tmpPath, $pdf);

        $im = new \Imagick();
        $im->setResolution(150, 150);
        $im->readImage($tmpPath.'[0]');
        $im = $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        $im->setImageFormat('jpeg');
        $im->setImageCompressionQuality(75);
        $im->cropThumbnailImage(512, 512);

        // تخزين مباشر داخل public/prints/
        $fileName = 'daily_courses_' . now()->format('Ymd_His') . '.jpg';
        $publicPath = public_path('prints/'.$fileName);
        $im->writeImage($publicPath);

        unlink($tmpPath);

        $imageUrl = url('prints/'.$fileName);

        $waapi->sendImage("120363302662559905@g.us", $imageUrl, 'جدول الكورسات اليوم');

        return self::SUCCESS;
    }
}
