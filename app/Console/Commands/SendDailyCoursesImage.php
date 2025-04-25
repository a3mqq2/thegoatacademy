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
    protected $signature = 'courses:send-daily-image';
    protected $description = 'Generate today’s course-schedule (A4), convert to image and send it via WhatsApp to the exam manager';

    public function handle(WaapiService $waapi): int
    {
        /* ------------------------------------------------------------------
         | 1. Fetch today’s courses (those that have any exam date == today)
         | -----------------------------------------------------------------*/
        $today = Carbon::today()->addDays(2);

        $courses = Course::with(['exams', 'courseType'])
            ->where(fn($q) => $q
                ->whereDate('pre_test_date',   $today)
                ->orWhereDate('mid_exam_date', $today)
                ->orWhereDate('final_exam_date',$today)
            )
            ->get();
        /* ------------------------------------------------------------------
         | 2. Render the Blade view to HTML
         | -----------------------------------------------------------------*/
        $instructors = User::role('Instructor')->get();
        $examiners   = User::role('Examiner')->get();
        $courseTypes = CourseType::all();
        $afterOne    = $today->copy()->addDay();

        $html = View::make('exam_officer.courses.print', compact(
            'courses', 'instructors', 'examiners', 'courseTypes', 'afterOne', 'today'
        ))->render();

        /* ------------------------------------------------------------------
         | 3. HTML → PDF  (A4 – 150 DPI for crisp text)
         | -----------------------------------------------------------------*/
        $pdfBin = Pdf::loadHTML($html)
                     ->setPaper('A4', 'portrait')
                     ->setOptions([
                         'dpi'                  => 150,
                         'isRemoteEnabled'      => true,
                         'isHtml5ParserEnabled' => true,
                     ])
                     ->output();

        $tmpPdf = storage_path('app/daily_courses.pdf');
        file_put_contents($tmpPdf, $pdfBin);

        /* ------------------------------------------------------------------
         | 4. PDF → JPG 1240×1754  (≈ A4 @ 150 ppi)
         | -----------------------------------------------------------------*/
        $img = new \Imagick();
        $img->setResolution(300, 300);           // read high-res, then downsize
        $img->readImage($tmpPdf . '[0]');
        $img->setImageBackgroundColor('white');
        $img = $img->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        $img->setImageFormat('jpg');
        $img->setImageCompressionQuality(90);
        $img->cropThumbnailImage(1240, 1754);    // keep A4 aspect 1:√2

        $fileName = 'prints/daily_courses_' . now()->format('Ymd_His') . '.jpg';
        Storage::disk('public')->put($fileName, $img);
        $publicUrl = asset('storage/' . $fileName);

        unlink($tmpPdf);   // clean temp file

        /* ------------------------------------------------------------------
         | 5. Send via WhatsApp (WAAPI)
         | -----------------------------------------------------------------*/
        $caption = '📋 جدول الامتحانات اليومي - ' . now()->format('Y-m-d');
        $waapi->sendImage(env('EXAM_MANAGER_CHATID'), $publicUrl, $caption);

        $this->info('Daily course image sent successfully.');
        return self::SUCCESS;
    }
}
