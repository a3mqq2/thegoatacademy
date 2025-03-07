<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseStudent extends Model
{
    use HasFactory;

    protected $table = 'course_students';

    protected $fillable = [
        'course_id',
        'student_id',
        'withdrawn_reason_id',
        'exclude_reason_id',
        'status',
    ];

    /**
     * Relation: Pivot table entry belongs to a Course.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Relation: Pivot table entry belongs to a Student.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function excludeReason()
    {
        return $this->belongsTo(ExcludeReason::class, 'exclude_reason_id');
    }

    public function withdrawnReason()
    {
        return $this->belongsTo(WithdrawnReason::class, 'withdrawn_reason_id');
    }

}
