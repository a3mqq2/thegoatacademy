<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CourseType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status', 'duration'];

    protected $casts = [
        'status'   => 'string',
        'duration' => 'string',
    ];

    public function isActive(): bool
    {
        return $this->status == 'active';
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => ucfirst(strtolower($value))
        );
    }

    /**
     * علاقة مع جميع المهارات (للتوافق مع النظام القديم)
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'course_type_skill', 'course_type_id', 'skill_id')
                    ->using(CourseTypeSkill::class)
                    ->withPivot(['id', 'mid_max', 'final_max', 'progress_test_max', 'skill_type'])
                    ->withTimestamps();
    }

    /**
     * علاقة مع مهارات Progress Test فقط
     */
    public function progressSkills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'course_type_skill', 'course_type_id', 'skill_id')
                    ->using(CourseTypeSkill::class)
                    ->wherePivot('skill_type', 'progress')
                    ->withPivot(['id', 'progress_test_max', 'skill_type'])
                    ->withTimestamps();
    }

    /**
     * علاقة مع مهارات Mid & Final فقط
     */
    public function examSkills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'course_type_skill', 'course_type_id', 'skill_id')
                    ->using(CourseTypeSkill::class)
                    ->wherePivot('skill_type', 'exam')
                    ->withPivot(['id', 'mid_max', 'final_max', 'skill_type'])
                    ->withTimestamps();
    }

    /**
     * علاقة مع المهارات القديمة (للتوافق مع النظام القديم)
     */
    public function legacySkills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'course_type_skill', 'course_type_id', 'skill_id')
                    ->using(CourseTypeSkill::class)
                    ->wherePivot('skill_type', 'legacy')
                    ->withPivot(['id', 'mid_max', 'final_max', 'progress_test_max', 'skill_type'])
                    ->withTimestamps();
    }

    /**
     * الحصول على مهارات Progress Test مع درجاتها
     */
    public function getProgressSkillsWithGrades()
    {
        return $this->progressSkills()->get()->map(function ($skill) {
            return [
                'id' => $skill->id,
                'name' => $skill->name,
                'max_grade' => $skill->pivot->progress_test_max
            ];
        });
    }

    /**
     * الحصول على مهارات Mid & Final مع درجاتها
     */
    public function getExamSkillsWithGrades()
    {
        return $this->examSkills()->get()->map(function ($skill) {
            return [
                'id' => $skill->id,
                'name' => $skill->name,
                'mid_max' => $skill->pivot->mid_max,
                'final_max' => $skill->pivot->final_max
            ];
        });
    }

    /**
     * الحصول على جميع المهارات مجمعة حسب النوع
     */
    public function getAllSkillsByType()
    {
        return [
            'progress' => $this->getProgressSkillsWithGrades(),
            'exam' => $this->getExamSkillsWithGrades(),
            'legacy' => $this->legacySkills()->get()
        ];
    }

    /**
     * فحص إذا كان CourseType يحتوي على بيانات قديمة تحتاج تحديث
     */
    public function hasLegacyData(): bool
    {
        return $this->legacySkills()->exists();
    }

    /**
     * تحويل البيانات القديمة إلى النظام الجديد
     */
    public function migrateLegacyData(): bool
    {
        $legacySkills = $this->legacySkills()->get();
        
        if ($legacySkills->isEmpty()) {
            return true; // لا توجد بيانات قديمة للتحويل
        }

        \DB::transaction(function () use ($legacySkills) {
            foreach ($legacySkills as $skill) {
                $pivot = $skill->pivot;
                
                // إذا كانت المهارة تحتوي على progress_test_max فقط
                if ($pivot->progress_test_max && !$pivot->mid_max && !$pivot->final_max) {
                    $pivot->update(['skill_type' => 'progress']);
                }
                // إذا كانت المهارة تحتوي على mid_max أو final_max
                elseif ($pivot->mid_max || $pivot->final_max) {
                    $pivot->update(['skill_type' => 'exam']);
                }
                // إذا كانت المهارة تحتوي على كل الدرجات، نقسمها إلى سجلين
                elseif ($pivot->progress_test_max && ($pivot->mid_max || $pivot->final_max)) {
                    // إنشاء سجل للـ progress
                    $this->skills()->attach($skill->id, [
                        'skill_type' => 'progress',
                        'progress_test_max' => $pivot->progress_test_max,
                        'mid_max' => null,
                        'final_max' => null
                    ]);
                    
                    // تحديث السجل الحالي ليصبح exam
                    $pivot->update([
                        'skill_type' => 'exam',
                        'progress_test_max' => null
                    ]);
                }
            }
        });

        return true;
    }
}