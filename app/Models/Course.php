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
        'progress_test_day',
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



        /*****************************************************************
         *  Model:  app/Models/Course.php
         *  دالّة generateSchedule() بعد التعديل
         *  - عدد الحصص = CourseType->duration بالضبط
         *  - يحترم التواريخ المُدخَلة يدويًّا (pre / mid / final)
         *  - عند تغيير الـ MID يزيح كل الحصص اللاحقة والـ FINAL تلقائيًا
         *  - لا يتجاوز نصفُ الحصص قبل الـ MID قيمة floor(duration ÷ 2)
         *****************************************************************/


         public function generateSchedule()
         {
             /* ────── from / to times ────── */
             $first = $this->schedules()->orderBy('id')->first();
             $fromTime = $first?->from_time;
             $toTime   = $first?->to_time;
         
             if (!$fromTime) {
                 $parts    = array_map('trim', explode(' - ', (string) $this->time));
                 $fromTime = $parts[0] ?? null;
                 $toTime   = $parts[1] ?? null;
             }
         
             if (!$toTime && $fromTime) {
                 $minutes = (int) optional($this->groupType)->lesson_duration ?: 60;
                 $toTime  = Carbon::createFromFormat('H:i', $fromTime)->addMinutes($minutes)->format('H:i');
             }
         
             /* ────── days mapping ────── */
             $map = ['Sat'=>6,'Sun'=>0,'Mon'=>1,'Tue'=>2,'Wed'=>3,'Thu'=>4,'Fri'=>5];
             $selectedDays = collect(explode('-', (string) $this->days))
                 ->filter(fn ($l) => isset($map[$l]))
                 ->map(fn ($l) => $map[$l])
                 ->values()->all();
         
             $duration = (int) $this->courseType->duration;
             if (!$duration || !$selectedDays || !$fromTime) return;
         
             $half       = intdiv($duration, 2);
             $occupied   = [];
             $classDates = [];
         
             /* pre-test */
             $pre = $this->pre_test_date
                 ? Carbon::parse($this->pre_test_date)
                 : Carbon::parse($this->start_date);
         
             if ($pre->gt(Carbon::parse($this->start_date))) {
                 $pre = Carbon::parse($this->start_date);
             }
             $pre->setTime(0,0);
             $this->pre_test_date            = $pre->toDateString();
             $occupied[$pre->toDateString()] = true;
         
             /* first half */
             $cur = $pre->copy()->addDay();
             while (count($classDates) < $half) {
                 if (in_array($cur->dayOfWeek, $selectedDays, true) && !isset($occupied[$cur->toDateString()])) {
                     $classDates[]                      = $cur->copy();
                     $occupied[$cur->toDateString()]    = true;
                 }
                 $cur->addDay();
             }
         
             /* mid-exam */
             $mid = $this->mid_exam_date ? Carbon::parse($this->mid_exam_date)->startOfDay() : null;
             $ref = $classDates[$half-1]->copy()->addDay();
             if (!$mid || $mid->lte($ref)) $mid = $ref;
             while (!in_array($mid->dayOfWeek, $selectedDays, true) || isset($occupied[$mid->toDateString()])) {
                 $mid->addDay();
             }
             $this->mid_exam_date            = $mid->toDateString();
             $occupied[$mid->toDateString()] = true;
         
             /* second half */
             $cur = $mid->copy()->addDay();
             while (count($classDates) < $duration) {
                 if (in_array($cur->dayOfWeek, $selectedDays, true) && !isset($occupied[$cur->toDateString()])) {
                     $classDates[]                      = $cur->copy();
                     $occupied[$cur->toDateString()]    = true;
                 }
                 $cur->addDay();
             }
         
             /* final-exam */
             $lastLecture = end($classDates);
             $final       = $this->final_exam_date
                 ? Carbon::parse($this->final_exam_date)->startOfDay()
                 : $lastLecture->copy()->addDay();
             while ($final->dayOfWeek === Carbon::FRIDAY || $final->lte($lastLecture)) {
                 $final->addDay();
             }
             $this->final_exam_date            = $final->toDateString();
             $occupied[$final->toDateString()] = true;
         
             /* persist */
             $this->save();
             $this->schedules()->delete();
         
             foreach ($classDates as $i => $d) {
                 $this->schedules()->create([
                     'day'       => $d->format('l'),
                     'date'      => $d->toDateString(),
                     'from_time' => $fromTime,
                     'to_time'   => $toTime,
                     'order'     => $i + 1,
                 ]);
             }
         }
         
         

}
