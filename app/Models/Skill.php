<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = ['name'];

    public function students()
    {
        return $this->belongsToMany(Student::class)->withTimestamps();
    }

    /**
     * Relationship with CourseTypes using the same pivot table.
     */
    public function courseTypes()
    {
        return $this->belongsToMany(CourseType::class, 'course_type_skill', 'skill_id', 'course_type_id')
                    ->withPivot(['mid_max', 'final_max']);
    }
}
