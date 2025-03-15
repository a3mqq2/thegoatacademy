<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_type_id',
        'group_type_id',
        'instructor_id',
        'start_date',
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

}
