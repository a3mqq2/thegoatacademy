<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressTest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'date',
        'course_id',
    ];

    /**
     * Get the course associated with the progress test.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the student records (results) for the progress test.
     */
    public function progressTestStudents()
    {
        return $this->hasMany(ProgressTestStudent::class);
    }
}
