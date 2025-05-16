<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressTestStudentGrade extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'progress_test_student_grades';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'progress_test_student_id',
        'course_type_skill_id',
        'progress_test_grade',
        'max_grade',
    ];

    /**
     * Get the progress-test-student record that this grade belongs to.
     */
    public function progressTestStudent()
    {
        return $this->belongsTo(ProgressTestStudent::class);
    }

    /**
     * Get the course-type-skill associated with this grade.
     */
    public function courseTypeSkill()
    {
        return $this->belongsTo(CourseTypeSkill::class);
    }
}
