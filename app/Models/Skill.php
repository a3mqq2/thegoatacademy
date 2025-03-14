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


    public function courseTypes()
    {
        return $this->belongsToMany(CourseType::class, 'skill_course_types', 'skill_id', 'course_type_id');
    }

}
