<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'day',
        'date',
        'from_time',
        'to_time',
        'attendance_taken_at',
        'close_at',
        'alert_at',
        'status',
        'extra_date',
    ];

    /**
     * Relation: Each schedule entry belongs to a Course.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function attendances()
    {
        return $this->hasMany(CourseAttendance::class, 'course_schedule_id');
    }
}
