<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\ProgressTest;
use App\Models\CourseSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // 1) حدّث الإعدادات في الـ DB
        $settings = $request->settings;
        foreach ($settings as $key => $value) {
            Setting::where('key', $key)->update(['value' => $value]);
        }

        // 2) استخرج القيم بعد التحديث
        $progressWindow = (int) Setting::where('key', 'Allow updating progress tests after class end time (hours)')
            ->value('value');
        $progressAlert   = (int) Setting::where('key', 'Notify instructor after update grace period (hours)')
            ->value('value');

        $attendanceWindow = (int) Setting::where('key', 'Updating the students’ Attendance after the class.')
            ->value('value');
        $attendanceAlert   = (int) Setting::where('key', 'Notify instructor after update grace period (hours)')
            ->value('value');

        // 3) أعد حساب نوافذ التعديل والتنبيه لكل ProgressTest
        ProgressTest::all()->each(function($pt) use ($progressWindow, $progressAlert) {
            // close_at = تاريخ الاختبار + نافذة التعديل
            $closeAt     = Carbon::parse("{$pt->date} {$pt->time}")
                                ->addHours($progressWindow);
            // will_alert_at = close_at + نافذة التنبيه
            $willAlertAt = $closeAt->copy()->addHours($progressAlert);

            $pt->update([
                'close_at'      => $closeAt,
                'will_alert_at' => $willAlertAt,
            ]);
        });

        // 4) أعد حساب نوافذ الحصص لكل CourseSchedule
        CourseSchedule::all()->each(function($sch) use ($attendanceWindow, $attendanceAlert) {
            // close_at = end time of lecture + نافذة التعديل
            $lectureEnd = Carbon::parse("{$sch->date} {$sch->to_time}");
            $closeAt    = $lectureEnd->copy()->addHours($attendanceWindow);
            // alert_at = close_at + نافذة التنبيه
            $alertAt    = $closeAt->copy()->addHours($attendanceAlert);

            $sch->update([
                'close_at' => $closeAt,
                'alert_at' => $alertAt,
            ]);
        });

        return redirect()
            ->back()
            ->with('success', 'Settings updated and all windows recalculated successfully.');
    }
}
