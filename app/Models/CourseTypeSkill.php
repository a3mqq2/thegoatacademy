<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseTypeSkill extends Model
{
    protected $table = 'course_type_skill'; // your migration uses this table name

    protected $fillable = [
        'course_type_id',
        'skill_id',
        'mid_max',
        'final_max',
    ];

    /**
     * This pivot belongs to one course type.
     */
    public function courseType()
    {
        return $this->belongsTo(CourseType::class);
    }

    /**
     * This pivot belongs to one skill.
     */
    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    /**
     * A CourseTypeSkill record can be referenced by many exam student grade records.
     */
    public function examStudentGrades()
    {
        return $this->hasMany(ExamStudentGrade::class, 'course_type_skill_id');
    }
}
