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
             [$fromTime, $toTime] = array_map('trim', explode(' - ', $this->time));
         
             $selectedDays = collect(explode('-', $this->days))->map(function ($label) {
                 return [
                     'Sat' => Carbon::SATURDAY, 'Sun' => Carbon::SUNDAY, 'Mon' => Carbon::MONDAY,
                     'Tue' => Carbon::TUESDAY,  'Wed' => Carbon::WEDNESDAY, 'Thu' => Carbon::THURSDAY,
                     'Fri' => Carbon::FRIDAY,
                 ][$label];
             })->all();
         
             $duration = (int) $this->courseType->duration;
             $half = intdiv($duration, 2);
             $occupied = [];
         
             $classDates = [];
         
             // 1) pre test
             $pre = $this->pre_test_date
                 ? Carbon::parse($this->pre_test_date)
                 : Carbon::parse($this->start_date)->copy();
             $pre->setTime(0, 0);
             if ($pre->gt(Carbon::parse($this->start_date))) {
                 $pre = Carbon::parse($this->start_date);
             }
             $this->pre_test_date = $pre->toDateString();
             $occupied[$pre->toDateString()] = true;
         
             // 2) generate first half
             $cursor = $pre->copy()->addDay();
             while (count($classDates) < $half) {
                 if (in_array($cursor->dayOfWeek, $selectedDays) && !isset($occupied[$cursor->toDateString()])) {
                     $classDates[] = $cursor->copy();
                     $occupied[$cursor->toDateString()] = true;
                 }
                 $cursor->addDay();
             }
         
             // 3) mid exam
             $mid = $this->mid_exam_date ? Carbon::parse($this->mid_exam_date) : null;
             $mid = $mid ? $mid->copy()->setTime(0, 0) : null;
         
             if (!$mid) {
                 $mid = $classDates[$half - 1]->copy()->addDay();
                 while (
                     in_array($mid->dayOfWeek, $selectedDays) === false ||
                     isset($occupied[$mid->toDateString()])
                 ) {
                     $mid->addDay();
                 }
             } else {
                 // enforce mid is not before the suggested mid
                 $suggestedMid = $classDates[$half - 1]->copy()->addDay();
                 if ($mid->lt($suggestedMid)) {
                     $mid = $suggestedMid;
                 }
             }
         
             $this->mid_exam_date = $mid->toDateString();
             $occupied[$mid->toDateString()] = true;
         
             // 4) second half after mid
             $cursor = $mid->copy()->addDay();
             while (count($classDates) < $duration) {
                 if (in_array($cursor->dayOfWeek, $selectedDays) && !isset($occupied[$cursor->toDateString()])) {
                     $classDates[] = $cursor->copy();
                     $occupied[$cursor->toDateString()] = true;
                 }
                 $cursor->addDay();
             }
         
             // 5) final exam
             $final = $this->final_exam_date ? Carbon::parse($this->final_exam_date) : null;
             $lastLecture = end($classDates);
             $suggestedFinal = $lastLecture->copy()->addDay();
         
             if (!$final || $final->lte($lastLecture)) {
                 $final = $suggestedFinal;
             }
             $this->final_exam_date = $final->toDateString();
             $occupied[$final->toDateString()] = true;
         
             // 6) حفظ وتوليد الجدول
             $this->save();
             $this->schedules()->delete();
         
             foreach ($classDates as $index => $date) {
                 $this->schedules()->create([
                     'day'       => $date->format('l'),
                     'date'      => $date->toDateString(),
                     'from_time' => $fromTime,
                     'to_time'   => $toTime,
                     'order'     => $index + 1,
                 ]);
             }
         }
         

}
