<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseTypeSkill extends Model
{
    protected $table = 'course_type_skill';

    protected $fillable = [
        'course_type_id',
        'skill_id',
        'progress_test_max',
        'mid_max',
        'final_max',
    ];

    public function courseType()
    {
        return $this->belongsTo(CourseType::class);
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function examStudentGrades()
    {
        return $this->hasMany(ExamStudentGrade::class, 'course_type_skill_id');
    }


    public function progressTestStudentGrades()
    {
        return $this->hasMany(ProgressTestStudentGrade::class, 'course_type_skill_id');
    }
    
}
