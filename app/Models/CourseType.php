<?php
// app/Models/CourseType.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class CourseType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status', 'duration'];

    protected $casts = [
        'status'   => 'string',
        'duration' => 'string',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => ucfirst(strtolower($value))
        );
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'course_type_skill', 'course_type_id', 'skill_id')
                    ->withPivot(['id', 'mid_max', 'final_max']);
    }

    protected static function boot()
    {
        parent::boot();
    }
}
