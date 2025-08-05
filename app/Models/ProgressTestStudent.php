<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressTestStudent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'progress_test_id',
        'student_id',
        'course_id',
        'score',
        'status',
    ];

    /**
     * Get the progress test that this record belongs to.
     */
    public function progressTest()
    {
        return $this->belongsTo(ProgressTest::class);
    }

    /**
     * Get the student associated with this test record.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the course associated with this test record.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }


    public function grades()
    {
        return $this->hasMany(ProgressTestStudentGrade::class);
    }
}
