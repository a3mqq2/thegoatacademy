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
        $today   = Carbon::today()->addDays(2);                    // أو +2 أيام كما تريد
        $courses = Course::query()
                  ->whereDate('pre_test_date',   $today)
                  ->orWhereDate('mid_exam_date', $today)
                  ->orWhereDate('final_exam_date',$today)
                  ->get();
    
        /* خلفية البطاقة */
        $bgB64 = base64_encode(file_get_contents(public_path('images/cource.png')));
    
        /* HTML بطاقة 90 مم */
        $html  = View::make('exam_officer.courses.print', [
            'courses'=> $courses,
            'today'  => $today,
            'bgData' => $bgB64,
        ])->render();
    
        /* PDF مربع 90 mm */
        $pt   = 255.1;                                  // 90 mm بالبوينت
        $pdf  = Pdf::loadHTML($html)
                   ->setPaper([0,0,$pt,$pt])
                   ->setOptions([
                       'dpi'=>96,'isRemoteEnabled'=>true,'isHtml5ParserEnabled'=>true
                   ])->output();
        $tmp  = storage_path('app/tmp_card.pdf');
        file_put_contents($tmp,$pdf);
    
        /* Imagick → JPG واضح */
        $im = new \Imagick();
        $im->setResolution(300,300);
        $im->readImage($tmp.'[0]');
        $im = $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        $im->setImageFormat('jpg');
        $im->setImageCompressionQuality(92);
        $im->cropThumbnailImage(1020,1020);             // أو 512، 2040 حسب الحاجة
    
        $file = 'prints/daily_courses_'.now()->format('Ymd_His').'.jpg';
        Storage::disk('public')->put($file,$im);
        unlink($tmp);
    
        $waapi->sendImage(env('EXAM_MANAGER_CHATID'), asset('storage/'.$file));
        $this->info('Image ready → '.asset('storage/'.$file));
        return self::SUCCESS;
    }
    
}
