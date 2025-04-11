<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    // Define any fillable or guarded properties if needed.
    protected $fillable = [
        'course_id',
        'examiner_id',
        'exam_type',
        'exam_date',
        'status',
        // other fields...
    ];


    public $casts = [
        'exam_date' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(\App\Models\Course::class);
    }

    public function examiner()
    {
        return $this->belongsTo(\App\Models\User::class, 'examiner_id');
    }

    /**
     * Relationship: An Exam belongs to many Students.
     *
     * The relationship uses the pivot table "exam_students" defined in your migration.
     * By using withTimestamps(), we ensure the pivot table's created_at and updated_at fields are managed automatically.
     */
    public function students()
    {
        return $this->belongsToMany(\App\Models\Student::class, 'exam_students')
                    ->withTimestamps();
    }



        /**
         * One exam has many exam_student pivot records.
         */
        public function examStudents()
        {
            return $this->hasMany(ExamStudent::class);
        }


}
