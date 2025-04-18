<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_type_id',
        'group_type_id',
        'instructor_id',
        'start_date',
        'pre_test_date',
        'mid_exam_date',
        'final_exam_date',
        'end_date',
        'student_capacity',
        'status',
        'student_count',
        'days',
        'time',
        'meeting_platform_id',
        'whatsapp_group_link',
    ];

    /**
     * Relation: A course belongs to a user as the instructor
     * (assuming your 'instructor_id' references the users table).
     */
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Relation: A course has many schedule entries.
     */
    public function schedules()
    {
        return $this->hasMany(CourseSchedule::class);
    }

    /**
     * Relation: A course belongs to many students (Pivot: course_students).
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'course_students')
                    ->withPivot(['status', 'exclude_reason_id', 'withdrawn_reason_id'])
                    ->withTimestamps();
    }

    

    /**
     * Example: If you have separate models for course types or group types,
     * you can add these:
     */
    public function courseType()
    {
        return $this->belongsTo(CourseType::class);
    }

    public function groupType()
    {
        return $this->belongsTo(GroupType::class);
    }


    // audit logs for course

    public function logs()
    {
        return $this->hasMany(AuditLog::class, 'entity_id', 'id')
            ->where('entity_type', '=', self::class);
    }
    

    public function meetingPlatform()
    {
        return $this->belongsTo(MeetingPlatform::class);
    }


    public function levels()
    {
        return $this->belongsToMany(Level::class);
    }

    public function progressTests()
    {
        return $this->hasMany(ProgressTest::class);
    }


    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    /**
     * A Course has many ExamGrades through its Exams.
     */
    public function examGrades()
    {
        return $this->hasManyThrough(ExamGrade::class, Exam::class, 'course_id', 'exam_id');
    }

    public function generateSchedule(): void
    {
        // parse our lesson times
        [$fromTime, $toTime] = array_map('trim', explode(' - ', $this->time));
    
        // map day labels to Carbon dayOfWeek
        $labelToDay = [
            'Sat' => Carbon::SATURDAY,
            'Sun' => Carbon::SUNDAY,
            'Mon' => Carbon::MONDAY,
            'Tue' => Carbon::TUESDAY,
            'Wed' => Carbon::WEDNESDAY,
            'Thu' => Carbon::THURSDAY,
            'Fri' => Carbon::FRIDAY,
        ];
    
        // which days are selected? stored as "Sat-Mon-Wed"
        $selected = array_filter(
            explode('-', $this->days),
            fn($lbl) => isset($labelToDay[$lbl])
        );
        $selectedDays = array_map(fn($lbl) => $labelToDay[$lbl], $selected);
    
        // helper to skip Fridays
        $skipIfFriday = function(Carbon $d) {
            return $d->dayOfWeek === Carbon::FRIDAY
                ? $d->addDay()
                : $d;
        };
    
        // occupied dates
        $occupied = [];
    
        // 1) pre-test date: if already set, parse it; otherwise day before start_date
        $pre = $this->pre_test_date
            ? Carbon::parse($this->pre_test_date)
            : Carbon::parse($this->start_date)->subDay();
        $pre = $skipIfFriday($pre);
        $this->pre_test_date = $pre->toDateString();
        $occupied[$this->pre_test_date] = true;
    
        // start scheduling classes the day after pre-test
        $cursor = $pre->copy()->addDay();
        $cursor = $skipIfFriday($cursor);
    
        $totalClasses = (int) $this->courseType->duration;
        $firstHalf    = (int) floor($totalClasses / 2);
    
        $classDates = [];
    
        // 2) first half
        while (count($classDates) < $firstHalf) {
            $key = $cursor->toDateString();
            if (in_array($cursor->dayOfWeek, $selectedDays) && !isset($occupied[$key])) {
                $classDates[]     = $cursor->copy();
                $occupied[$key]   = true;
            }
            $cursor->addDay();
            $cursor = $skipIfFriday($cursor);
        }
    
        // 3) mid-exam
        $mid = $cursor->copy();
        while (isset($occupied[$mid->toDateString()])) {
            $mid->addDay();
            $mid = $skipIfFriday($mid);
        }
        $this->mid_exam_date = $mid->toDateString();
        $occupied[$this->mid_exam_date] = true;
    
        // advance past mid-exam
        $cursor = $mid->copy()->addDay();
        $cursor = $skipIfFriday($cursor);
    
        // 4) second half
        $secondNeeded = $totalClasses - $firstHalf;
        $generated    = 0;
        while ($generated < $secondNeeded) {
            $key = $cursor->toDateString();
            if (in_array($cursor->dayOfWeek, $selectedDays) && !isset($occupied[$key])) {
                $classDates[]     = $cursor->copy();
                $occupied[$key]   = true;
                $generated++;
            }
            $cursor->addDay();
            $cursor = $skipIfFriday($cursor);
        }
    
        // 5) final exam
        $final = $cursor->copy();
        while (isset($occupied[$final->toDateString()])) {
            $final->addDay();
            $final = $skipIfFriday($final);
        }
        $this->final_exam_date = $final->toDateString();
    
        // persist dates
        $this->save();
    
        // rebuild schedules
        $this->schedules()->delete();
        foreach ($classDates as $i => $date) {
            $this->schedules()->create([
                'day'       => $date->format('l'),          // "Monday"
                'date'      => $date->toDateString(),       // "YYYY-MM-DD"
                'from_time' => $fromTime,
                'to_time'   => $toTime,
                'order'     => $i + 1,
            ]);
        }
    }
}
