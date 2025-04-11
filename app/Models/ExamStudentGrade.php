<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamStudentGrade extends Model
{
    protected $table = 'exam_student_grades';

    protected $fillable = [
        'exam_student_id',
        'course_type_skill_id',
        'grade',
    ];

    /**
     * The grade belongs to an ExamStudent record.
     */
    public function examStudent()
    {
        return $this->belongsTo(ExamStudent::class);
    }

    /**
     * The grade is associated with a course type skill.
     */
    public function courseTypeSkill()
    {
        return $this->belongsTo(CourseTypeSkill::class, 'course_type_skill_id');
    }
}
