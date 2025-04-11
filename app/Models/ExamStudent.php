<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamStudent extends Model
{
    protected $table = 'exam_students';

    protected $fillable = [
        'exam_id',
        'student_id',
    ];

    /**
     * The exam-student pivot record belongs to one exam.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * The exam-student pivot record belongs to one student.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Each exam-student record may have many associated grade details.
     */
    public function grades()
    {
        return $this->hasMany(ExamStudentGrade::class, 'exam_student_id');
    }
}
