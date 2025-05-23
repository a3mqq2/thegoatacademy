<?php
/*──────────────────────────────────────────────────────────────
 | App\Http\Controllers\Instructor\DashboardController
 | -----------------------------------------------------------------
 | ـ هذا الملف يشغِّل الـ Dashboard الخاص بالمدرّس ويتكفّل
 |   بجلب كل البيانات (الكورسات – الجداول – الاختبارات …إلخ)
 |   مع الأخذ فى الاعتبار مهلة إدخال الحضور المعرَّفة فى الإعدادات.
 *──────────────────────────────────────────────────────────────*/
namespace App\Http\Controllers\Instructor;

use Carbon\Carbon;
use App\Models\Setting;
use App\Models\ProgressTest;
use App\Models\CourseSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Display the instructor dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // 1) Counters
        $ongoing_courses   = $user->courses()->where('status', 'ongoing')->count();
        $completed_courses = $user->courses()->where('status', 'completed')->count();

        $today   = Carbon::now()->toDateString();
        $weekday = Carbon::now()->dayOfWeek;

        $rawTests = ProgressTest::whereNull('done_at')
            ->whereDate('date', $today)
            ->whereHas('course', function($q) use($user) {
                $q->where('instructor_id', $user->id) 
                  ->where('status', 'ongoing');
            })
            ->whereRaw(
                "STR_TO_DATE(CONCAT(`date`,' ',`time`),'%Y-%m-%d %H:%i') <= ?",
                [ now()->format('Y-m-d H:i') ]
            )
            ->where('close_at', '>=', now())
            ->get();

        // 4) Today’s schedules still within their attendance window
        $rawSchedules = CourseSchedule::whereDate('date', $today)
            ->whereHas('course', fn($q) => $q->where('instructor_id', $user->id))
            ->get();
        $pendingSchedules = $rawSchedules
            ->whereNull('attendance_taken_at')
            ->where('close_at', '>', now())
            ->values();

        // 5) Courses that need a progress-test today
        $coursesDueProgress = $user->courses()
            ->where('status', 'ongoing')
            ->where('progress_test_day', $weekday)
            ->whereDoesntHave('progressTests', fn($q) => $q->where('date', $today))
            ->get();

        // 6) Missed lectures last week, but only if today is this course's progress_test_day
        $weekStart = Carbon::now()->startOfWeek(Carbon::SATURDAY)->subWeek();
        $weekEnd   = (clone $weekStart)->addDays(5);
        $todayDow  = Carbon::now()->dayOfWeek;

        $missedLastWeek = CourseSchedule::whereNull('attendance_taken_at')
            ->where('status', 'absent')
            ->whereDate('date', '>=', Carbon::now()->subDays(6)->toDateString())  
            ->whereHas('course', function($q) use ($user, $todayDow) {
                $q->where('instructor_id', $user->id);
                $q->where('progress_test_day', $todayDow);
                $q->where('status','ongoing');
            })
            ->get();

        return view('instructor.dashboard', compact(
            'ongoing_courses',
            'completed_courses',
            'rawTests',
            'pendingSchedules',
            'coursesDueProgress',
            'missedLastWeek'
        ));
    }

    /**
     * Show instructor profile page.
     */
    public function profile()
    {
        return view('instructor.profile');
    }

    /**
     * Update instructor profile.
     */
    public function profile_update(Request $request)
    {
        $user = Auth::user();

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

        $validated = $request->validate($rules);

        $user->fill([
            'name'  => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'age'   => $validated['age']   ?? $user->age,
            'video' => $validated['video'] ?? $user->video,
            'notes' => $validated['notes'] ?? $user->notes,
        ]);

        if (!empty($validated['password'])) {
            $user->password = \Hash::make($validated['password']);
        }

        if ($request->hasFile('avatar')) {
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        // Update shifts
        $user->shifts()->delete();
        if ($request->has('shifts')) {
            foreach (array_chunk($request->shifts, 3) as $shift) {
                [$dayEntry, $startEntry, $endEntry] = $shift;
                if (!empty($dayEntry['day']) && !empty($startEntry['start_time']) && !empty($endEntry['end_time'])) {
                    $user->shifts()->create([
                        'day'        => $dayEntry['day'],
                        'start_time' => $startEntry['start_time'],
                        'end_time'   => $endEntry['end_time'],
                    ]);
                }
            }
        }

        return redirect()
            ->route('instructor.profile')
            ->with('success', 'Profile updated successfully.');
    }
}
