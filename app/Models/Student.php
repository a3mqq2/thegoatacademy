<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'status', 'withdrawal_reason', 'books_due','city', 'age', 'specialization','gender','avatar','emergency_phone'];

    protected $casts = [
        'books_due' => 'boolean',
    ];

    public function isOngoing(): bool
    {
        return $this->status === 'ongoing';
    }

    public function isExcluded(): bool
    {
        return $this->status === 'excluded';
    }

    public function isWithdrawn(): bool
    {
        return $this->status === 'withdrawn';
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_students')
                    ->withPivot(['status', 'withdrawn_reason_id', 'exclude_reason_id'])
                    ->withTimestamps();
    }
    

    public function skills()
    {
        return $this->belongsToMany(Skill::class)->withTimestamps();
    }


    public function files()
    {
        return $this->hasMany(StudentFile::class);
    }
}
