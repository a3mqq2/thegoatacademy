<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class CourseType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status', 'duration'];

    protected $casts = [
        'status' => 'string',
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

    /**
     * Relationship with skills, including pivot grade data.
     */
    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'course_type_skill', 'course_type_id', 'skill_id')
                    ->withPivot(['mid_max', 'final_max']);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($courseType) {
            // Additional logic before creating a course type
        });

        static::updating(function ($courseType) {
            // Additional logic before updating a course type
        });

        static::deleting(function ($courseType) {
            // Prevent deletion if necessary
        });
    }
}
