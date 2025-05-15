<?php
/*──────────────────────────────────────────────────────────────
 | App\Http\Controllers\Instructor\DashboardController
 | -----------------------------------------------------------------
 | ـ هذا الملف يشغِّل الـ Dashboard الخاص بالمدرّس ويتكفّل
 |   بجلب كل البيانات (الكورسات – الجداول – الاختبارات …إلخ)
 |   مع الأخذ فى الاعتبار مهلة إدخال الحضور المعرَّفة فى الإعدادات.
 *──────────────────────────────────────────────────────────────*/
namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\CourseSchedule;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /*──────────────────────── index ────────────────────────*/
    public function index()
    {
        /*— عدد الكورسات الجارية —*/
        $ongoing_courses = auth()->user()
            ->courses()
            ->where('status', 'ongoing')
            ->count();

        /*— المهلة بالساعات المسموح بها بعد نهاية المحاضرة —*/
        $limitHrs = (int) (Setting::where('key', 'Updating the students’ Attendance after the class.')
                        ->value('value') ?? 0);

        /*══════════════ محاضرات اليوم (لم يُؤخذ حضورها) ══════════════*/
        $todayRaw = CourseSchedule::whereDate('date', now()->toDateString())
            ->whereNull('attendance_taken_at')
            ->whereHas('course', function ($q) {
                $q->where('instructor_id', auth()->id());
            })
            ->with('course')
            ->get();

        // إبقاء المحاضرات التى بدأت بالفعل ولم تنقضِ المهلة
        $schedules = $todayRaw->filter(function ($sch) use ($limitHrs) {
            $start   = Carbon::parse($sch->date . ' ' . $sch->from_time); // وقت البداية
            $closing = Carbon::parse($sch->date . ' ' . $sch->to_time)->addHours($limitHrs);

            return now()->greaterThanOrEqualTo($start)   // بدأت
                && now()->lessThanOrEqualTo($closing);    // والمهلة مستمرة
        })->values();

        /*══════════════ محاضرات الأسبوع الماضى (لم يُؤخذ حضورها) ══════════════*/
        $weekStart = Carbon::now()->startOfWeek(Carbon::SATURDAY)->subWeek();
        $weekEnd   = (clone $weekStart)->addDays(6);

        $prevRaw = CourseSchedule::whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->whereNull('attendance_taken_at')
            ->whereHas('course', function ($q) {
                $q->where('instructor_id', auth()->id());
            })
            ->with('course')
            ->get();

        $previousWeekSchedules = $prevRaw->filter(function ($sch) use ($limitHrs) {
            $start   = Carbon::parse($sch->date . ' ' . $sch->from_time);
            $closing = Carbon::parse($sch->date . ' ' . $sch->to_time)->addHours($limitHrs);

            return now()->greaterThanOrEqualTo($start)
                && now()->lessThanOrEqualTo($closing);
        })->values();

        /*══════════════ الكورسات التى لم يُضف لها Progress-Test لهذا الأسبوع ══════════════*/
        $progressTestDate = Carbon::now(); // الخميس (أو الجمعة تحسب خميس حسب السياسة)
        if ($progressTestDate->isFriday()) {
            $progressTestDate = $progressTestDate->subDay();
        }
        $progressTestDate = $progressTestDate->toDateString();

        $coursesNeedsProgressTest = auth()->user()->courses()
            ->where('status', 'ongoing')
            ->whereDoesntHave('progressTests', function ($q) use ($progressTestDate) {
                $q->where('date', $progressTestDate);
            })
            ->get();

        /*══════════════ إرجاع الـ view ══════════════*/
        return view('instructor.dashboard', compact(
            'ongoing_courses',
            'schedules',
            'previousWeekSchedules',
            'coursesNeedsProgressTest'
        ));
    }

    /*──────────────────────── الملف الشخصـى ────────────────────────*/
    public function profile()
    {
        return view('instructor.profile');
    }

    /*──────────────────────── تحديث الملف الشخصـى ────────────────────────*/
    public function profile_update(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone'    => 'required|string|max:20',
            'avatar'   => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'password' => 'nullable|string|min:6|confirmed',
            'age'      => 'nullable|integer|min:1',
            'video'    => 'nullable|url',
            'notes'    => 'nullable|string',
            'shifts'   => 'nullable|array',
        ];

        $validated       = $request->validate($rules);
        $user->name      = $validated['name'];
        $user->email     = $validated['email'];
        $user->phone     = $validated['phone'];
        $user->age       = $validated['age']   ?? $user->age;
        $user->video     = $validated['video'] ?? $user->video;
        $user->notes     = $validated['notes'] ?? $user->notes;

        if (!empty($validated['password'])) {
            $user->password = \Hash::make($validated['password']);
        }
        if ($request->hasFile('avatar')) {
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }
        $user->save();

        /*—— تحديث الورديات ——*/
        $user->shifts()->delete();
        if ($request->has('shifts')) {
            foreach (array_chunk($request->shifts, 3) as $shift) {
                $day = $shift[0]['day'] ?? null;
                $st  = $shift[1]['start_time'] ?? null;
                $et  = $shift[2]['end_time']   ?? null;

                if ($day && $st && $et) {
                    $user->shifts()->create([
                        'day'        => $day,
                        'start_time' => $st,
                        'end_time'   => $et,
                    ]);
                }
            }
        }

        return redirect()
            ->route('instructor.profile')
            ->with('success', 'Profile updated successfully.');
    }
}
