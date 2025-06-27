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
        'time',
        'course_id',
        'week',
        'close_at',
        'done_at',
        'done_by',
        'will_alert_at',
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

    /**
     * الحصول على المهارات المتاحة لهذا الـ Progress Test
     */
    public function getAvailableSkills()
    {
        return $this->course->courseType->skills()
            ->where(function($query) {
                $query->where('skill_type', 'progress')
                      ->orWhere(function($subQuery) {
                          $subQuery->where('skill_type', 'legacy')
                                   ->whereNotNull('progress_test_max');
                      });
            })
            ->get();
    }

    /**
     * حساب إجمالي الدرجة القصوى للـ Progress Test
     */
    public function getTotalMaxScore()
    {
        return $this->getAvailableSkills()->sum(function($skill) {
            return $skill->pivot->progress_test_max ?? 0;
        });
    }

    /**
     * التحقق من أن الـ Progress Test مكتمل
     */
    public function isCompleted()
    {
        return !is_null($this->done_at);
    }

    /**
     * التحقق من أن نافذة التحرير مغلقة
     */
    public function isClosed()
    {
        return $this->close_at && now()->gte($this->close_at);
    }

    /**
     * الحصول على إحصائيات الـ Progress Test
     */
    public function getStatistics()
    {
        $students = $this->progressTestStudents;
        $totalMaxScore = $this->getTotalMaxScore();
        
        if ($students->isEmpty() || $totalMaxScore == 0) {
            return [
                'total_students' => 0,
                'completed_students' => 0,
                'average_score' => 0,
                'average_percentage' => 0,
                'pass_count' => 0,
                'fail_count' => 0,
                'pass_rate' => 0
            ];
        }

        $completedStudents = $students->filter(function($student) {
            return $student->grades->isNotEmpty();
        });

        $averageScore = $completedStudents->avg('score') ?? 0;
        $averagePercentage = $totalMaxScore > 0 ? ($averageScore / $totalMaxScore) * 100 : 0;
        
        $passCount = $completedStudents->filter(function($student) use ($totalMaxScore) {
            return $totalMaxScore > 0 && (($student->score / $totalMaxScore) * 100) >= 50;
        })->count();

        return [
            'total_students' => $students->count(),
            'completed_students' => $completedStudents->count(),
            'average_score' => round($averageScore, 2),
            'average_percentage' => round($averagePercentage, 2),
            'pass_count' => $passCount,
            'fail_count' => $completedStudents->count() - $passCount,
            'pass_rate' => $completedStudents->count() > 0 ? round(($passCount / $completedStudents->count()) * 100, 2) : 0
        ];
    }
}