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

        public function generateSchedule(): void
        {
            /* ---------- 0) وقت الدرس ---------- */
            [$fromTime, $toTime] = array_map('trim', explode(' - ', $this->time));

            /* ---------- 1) خريطة الأيام ---------- */
            $labelToDay = [
                'Sat' => Carbon::SATURDAY, 'Sun' => Carbon::SUNDAY, 'Mon' => Carbon::MONDAY,
                'Tue' => Carbon::TUESDAY,  'Wed' => Carbon::WEDNESDAY, 'Thu' => Carbon::THURSDAY,
                'Fri' => Carbon::FRIDAY,
            ];
            $selectedDays = collect(explode('-', $this->days))
                ->filter(fn ($l) => isset($labelToDay[$l]))
                ->map(fn ($l) => $labelToDay[$l])
                ->values()
                ->all();

            $skipFri = fn (Carbon $d) => $d->dayOfWeek === Carbon::FRIDAY ? $d->addDay() : $d;

            /* ---------- 2) ثَبِّت الامتحانات ---------- */
            $pre = $this->pre_test_date
                ? Carbon::parse($this->pre_test_date)
                : Carbon::parse($this->start_date)->subDay();
            $pre = $skipFri($pre);
            $this->pre_test_date = $pre->toDateString();

            $mid   = $this->mid_exam_date   ? $skipFri(Carbon::parse($this->mid_exam_date))   : null;
            $final = $this->final_exam_date ? $skipFri(Carbon::parse($this->final_exam_date)) : null;

            /* ---------- 3) توليد الحصص ---------- */
            $totalClasses = (int) $this->courseType->duration;          // ✅ مأخوذ من CourseType
            $firstHalf    = intdiv($totalClasses, 2);                   // الحد الأعلى قبل الـ MID
            $classDates   = [];
            $occupied     = [$pre->toDateString() => true];

            // ـ أ) حصص قبل الـ MID (بحد أقصى firstHalf)
            $cursor = $skipFri($pre->copy()->addDay());
            while (
                $mid &&                                            // يوجد MID يدوي
                $cursor->lt($mid) &&                               // ما زلنا قبل الـ MID
                count($classDates) < $firstHalf                    // لم نتجاوز الحد
            ) {
                if (
                    in_array($cursor->dayOfWeek, $selectedDays) &&
                    empty($occupied[$cursor->toDateString()])
                ) {
                    $classDates[]                 = $cursor->copy();
                    $occupied[$cursor->toDateString()] = true;
                }
                $cursor = $skipFri($cursor->addDay());
            }

            // ـ ب) إذا لم يُحدَّد MID يدويًّا احسبه الآن
            if (!$mid) {
                while (
                    isset($occupied[$cursor->toDateString()]) ||
                    $cursor->dayOfWeek === Carbon::FRIDAY
                ) {
                    $cursor = $skipFri($cursor->addDay());
                }
                $mid = $cursor->copy();
            }
            $this->mid_exam_date = $mid->toDateString();
            $occupied[$mid->toDateString()] = true;

            // ـ ج) الحصص بعد الـ MID حتى نبلغ العدد المطلوب
            $cursor = $skipFri($mid->copy()->addDay());
            while (count($classDates) < $totalClasses) {

                // لا تضَع حصة فوق يوم الـ FINAL اليدوي
                if ($final && $cursor->isSameDay($final)) {
                    $cursor = $skipFri($cursor->addDay());
                    continue;
                }

                if (
                    in_array($cursor->dayOfWeek, $selectedDays) &&
                    empty($occupied[$cursor->toDateString()])
                ) {
                    $classDates[]                 = $cursor->copy();
                    $occupied[$cursor->toDateString()] = true;
                }

                $cursor = $skipFri($cursor->addDay());
            }

            /* ---------- 4) اضبط الـ FINAL ليوم بعد آخر حصة ---------- */
            $lastLecture  = end($classDates);
            $desiredFinal = $skipFri($lastLecture->copy()->addDay());

            if (!$final || $final->lte($lastLecture)) {
                $final = $desiredFinal;
            }
            $this->final_exam_date = $final->toDateString();

            /* ---------- 5) حفظ و بناء جدول schedules ---------- */
            $this->save();

            $this->schedules()->delete();
            foreach ($classDates as $idx => $date) {
                $this->schedules()->create([
                    'day'       => $date->format('l'),          // Monday …
                    'date'      => $date->toDateString(),       // YYYY‑MM‑DD
                    'from_time' => $fromTime,
                    'to_time'   => $toTime,
                    'order'     => $idx + 1,
                ]);
            }
        }

}
