<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class GroupType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'student_capacity', 'status','lesson_duration'];

    protected $casts = [
        'student_capacity' => 'integer',
        'status' => 'string',
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

    public static function boot()
    {
        parent::boot();

        static::creating(function ($groupType) {
            // Additional logic before creating a group type
        });

        static::updating(function ($groupType) {
            // Additional logic before updating a group type
        });

        static::deleting(function ($groupType) {
            // Prevent deletion if necessary
        });
    }
}
