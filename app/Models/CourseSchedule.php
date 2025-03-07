<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'day',
        'date',
        'from_time',
        'to_time'
    ];

    /**
     * Relation: Each schedule entry belongs to a Course.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
