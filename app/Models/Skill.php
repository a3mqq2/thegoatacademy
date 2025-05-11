<?php
// app/Models/Skill.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = ['name'];

    public function students()
    {
        return $this->belongsToMany(Student::class)->withTimestamps();
    }

    public function courseTypes()
    {
        return $this->belongsToMany(CourseType::class, 'course_type_skill', 'skill_id', 'course_type_id')
                    ->withPivot(['id', 'mid_max', 'final_max']);
    }
}
