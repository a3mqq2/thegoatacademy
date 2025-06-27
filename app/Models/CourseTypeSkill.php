<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CourseTypeSkill extends Pivot
{
    protected $table = 'course_type_skill';

    protected $fillable = [
        'course_type_id',
        'skill_id',
        'skill_type',           // العمود الجديد
        'progress_test_max',
        'mid_max',
        'final_max',
    ];

    // Cast العمود الجديد
    protected $casts = [
        'skill_type' => 'string',
        'progress_test_max' => 'decimal:2',
        'mid_max' => 'decimal:2',
        'final_max' => 'decimal:2',
    ];

    // الثوابت لأنواع المهارات
    const SKILL_TYPE_PROGRESS = 'progress';
    const SKILL_TYPE_EXAM = 'exam';
    const SKILL_TYPE_LEGACY = 'legacy';

    public function courseType()
    {
        return $this->belongsTo(CourseType::class);
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function examStudentGrades()
    {
        return $this->hasMany(ExamStudentGrade::class, 'course_type_skill_id');
    }

    public function progressTestStudentGrades()
    {
        return $this->hasMany(ProgressTestStudentGrade::class, 'course_type_skill_id');
    }

    // دوال مساعدة للتحقق من نوع المهارة
    public function isProgressSkill(): bool
    {
        return $this->skill_type === self::SKILL_TYPE_PROGRESS;
    }

    public function isExamSkill(): bool
    {
        return $this->skill_type === self::SKILL_TYPE_EXAM;
    }

    public function isLegacySkill(): bool
    {
        return $this->skill_type === self::SKILL_TYPE_LEGACY;
    }

    // Scope للبحث حسب نوع المهارة
    public function scopeProgressSkills($query)
    {
        return $query->where('skill_type', self::SKILL_TYPE_PROGRESS);
    }

    public function scopeExamSkills($query)
    {
        return $query->where('skill_type', self::SKILL_TYPE_EXAM);
    }

    public function scopeLegacySkills($query)
    {
        return $query->where('skill_type', self::SKILL_TYPE_LEGACY);
    }
}