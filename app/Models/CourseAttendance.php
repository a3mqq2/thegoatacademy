<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'student_id',
        'course_schedule_id',
        'attendance',
        'homework_submitted',
        'notes',
    ];

    /**
     * Relations
     */

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function schedule()
    {
        return $this->belongsTo(CourseSchedule::class, 'course_schedule_id');
    }

    /**
     * Accessors (optional)
     */

    public function isPresent()
    {
        return $this->attendance === 'present';
    }

    public function isAbsent()
    {
        return $this->attendance === 'absent';
    }

    public function hasHomework()
    {
        return $this->homework_submitted;
    }
}
