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
        'week',
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


    public function grades()
    {
        return $this->hasManyThrough(
            ProgressTestStudentGrade::class,
            ProgressTestStudent::class,
            'progress_test_id',            // Foreign key on ProgressTestStudent
            'progress_test_student_id',    // Foreign key on ProgressTestStudentGrade
            'id',                          // Local key on ProgressTest
            'id'                           // Local key on ProgressTestStudent
        );
    }
}
