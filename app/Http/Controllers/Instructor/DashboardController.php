<?php

namespace App\Http\Controllers\Instructor;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\CourseSchedule;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $ongoing_courses = auth()->user()->courses()->where('status', 'ongoing')->count();

        // All schedules of ongoing courses where date is today and attendance is not taken
        $schedules = CourseSchedule::whereDate('date', now()->toDateString())
            ->whereHas('course', function($q) {
                $q->where('instructor_id', auth()->id());
            })
            ->whereNull('attendance_taken_at')
            ->with('course')
            ->get();

        // Get the previous week's range from Saturday to (Saturday + 6 days)
        $start = Carbon::now()->startOfWeek(Carbon::SATURDAY)->subWeek();
        $end   = (clone $start)->addDays(6);

        $previousWeekSchedules = CourseSchedule::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereNull('attendance_taken_at')
            ->whereHas('course', function($q) {
                $q->where('instructor_id', auth()->id());
            })
            ->with('course')
            ->get();

        // Determine progress test date if today is Thursday or Friday.
        // According to policy, if today is Friday, record as Thursday.
        $today = Carbon::now();
        $progressTestDate = $today;
        // if ($today->isThursday()) {
        //     $progressTestDate = $today->toDateString();
        // } elseif ($today->isFriday()) {
        //     // For Friday, set progress test date as Thursday.
        //     $progressTestDate = $today->subDay()->toDateString();
        // }

        // Fetch all courses that need a progress test.
        // That is, ongoing courses that do NOT have a progress test on the computed Thursday.
        $coursesNeedsProgressTest = collect();
        if ($progressTestDate) {
            $coursesNeedsProgressTest = auth()->user()->courses()
                ->where('status', 'ongoing')
                ->whereDoesntHave('progressTests', function ($q) use ($progressTestDate) {
                    $q->where('date', $progressTestDate);
                })
                ->get();
        }

        return view('instructor.dashboard', compact(
            'ongoing_courses',
            'schedules',
            'previousWeekSchedules',
            'coursesNeedsProgressTest'
        ));
    }


    public function profile()
    {
        return view("instructor.profile");
    }



  /**
     * Update the authenticated instructor's profile.
     */
    public function profile_update(Request $request)
    {
        $user = auth()->user();
    
        $rules = [
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone'          => 'required|string|max:20',
            'avatar'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'password'       => 'nullable|string|min:6|confirmed',
            'age'            => 'nullable|integer|min:1',
            'video'          => 'nullable|url',
            'notes'          => 'nullable|string',
            'shifts'         => 'nullable|array',
        ];
    
        $validated = $request->validate($rules);
    
        $user->name  = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];
    
        if (!empty($validated['password'])) {
            $user->password = \Hash::make($validated['password']);
        }
    
        if ($request->hasFile('avatar')) {
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }
    
        $user->age   = $validated['age']   ?? $user->age;
        $user->video = $validated['video'] ?? $user->video;
        $user->notes = $validated['notes'] ?? $user->notes;
    
        $user->save();
    
        $user->shifts()->delete();
        if ($request->has('shifts')) {
            // تقسيم العناصر لـمجموعات من 3 (day, start_time, end_time)
            $chunkedShifts = array_chunk($request->shifts, 3);
            foreach ($chunkedShifts as $shiftGroup) {
                $day        = $shiftGroup[0]['day']        ?? null;
                $start_time = $shiftGroup[1]['start_time'] ?? null;
                $end_time   = $shiftGroup[2]['end_time']   ?? null;
                
                if ($day && $start_time && $end_time) {
                    $user->shifts()->create([
                        'day'        => $day,
                        'start_time' => $start_time,
                        'end_time'   => $end_time,
                    ]);
                }
            }
        }
    
        return redirect()
            ->route('instructor.profile')
            ->with('success', 'Profile updated successfully.');
    }
}
