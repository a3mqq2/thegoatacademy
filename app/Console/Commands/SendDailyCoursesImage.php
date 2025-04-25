<?php
/**  app/Console/Commands/SendDailyCoursesImage.php */

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
    /** artisan courses:send-daily-image */
    protected $signature   = 'courses:send-daily-image';
    protected $description = 'Generate today’s course schedule (A4), convert it to an image, and send via WhatsApp';

    public function handle(WaapiService $waapi): int
    {
        /* ------------------------------------------------------------------
         | 1. Collect today’s courses
         | -----------------------------------------------------------------*/
        $today   = Carbon::today()->addDays(2);
        $courses = Course::with(['exams', 'courseType'])
            ->where(fn($q) => $q
                ->whereDate('pre_test_date',   $today)
                ->orWhereDate('mid_exam_date', $today)
                ->orWhereDate('final_exam_date',$today)
            )
            ->get();

    

        /* ------------------------------------------------------------------
         | 2. Render Blade to HTML
         | -----------------------------------------------------------------*/
        $html = View::make('exam_officer.courses.print', [
            'courses'      => $courses,
            'instructors'  => User::role('Instructor')->get(),
            'examiners'    => User::role('Examiner')->get(),
            'courseTypes'  => CourseType::all(),
            'afterOne'     => $today->copy()->addDay(),
            'today'        => $today,
        ])->render();

        /* ------------------------------------------------------------------
         | 3. HTML → PDF  (A4 portrait, 150 DPI)
         | -----------------------------------------------------------------*/
        $pdfBin = Pdf::loadHTML($html)
                     ->setPaper('A4','portrait')
                     ->setOptions([
                         'dpi'                  => 150,
                         'isRemoteEnabled'      => true,
                         'isHtml5ParserEnabled' => true,
                     ])
                     ->output();

        $tmpPdf = storage_path('app/daily_courses.pdf');
        file_put_contents($tmpPdf, $pdfBin);

        /* ------------------------------------------------------------------
         | 4. First PDF page → JPG, trimmed & resized
         | -----------------------------------------------------------------*/
        $im = new \Imagick();
        $im->setResolution(300, 300);              // read high-res
        $im->readImage($tmpPdf.'[0]');
        $im->setImageBackgroundColor('white');
        $im = $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);

        /* remove white margins around content */
        $im->trimImage(5);
        $im->setImagePage(0, 0, 0, 0);

        /* sharpen slightly then resize thumbnail */
        $im->sharpenImage(0, 1);
        $im->setImageFormat('jpg');
        $im->setImageCompressionQuality(90);

        // A4 aspect ratio thumbnail 1240×1754  (≈150 ppi)
        $im->cropThumbnailImage(1240, 1754);

        $fileName = 'prints/daily_courses_' . now()->format('Ymd_His') . '.jpg';
        Storage::disk('public')->put($fileName, $im);
        $publicUrl = asset('storage/' . $fileName);

        unlink($tmpPdf); // tidy up

        /* ------------------------------------------------------------------
         | 5. Send via WhatsApp WAAPI
         | -----------------------------------------------------------------*/
        $caption = '📋 جدول الامتحانات – ' . $today->format('Y-m-d');
        $waapi->sendImage(env('EXAM_MANAGER_CHATID'), $publicUrl, $caption);

        $this->info('Daily course image created and sent successfully.');
        return self::SUCCESS;
    }
}
