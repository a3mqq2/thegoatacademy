<?php

namespace App\Http\Controllers\Admin;

use App\Models\CourseType;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CourseTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = CourseType::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('duration')) {
            $query->where('duration', $request->duration);
        }


        
        $courseTypes = $query->paginate(10);

        return view('admin.course_types.index', compact('courseTypes'));
    }

    public function create()
    {
        $skills = Skill::all();
        return view('admin.course_types.create', compact('skills'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                          => 'required|string|max:255|unique:course_types',
            'status'                        => 'required|in:active,inactive',
            'duration'                      => 'nullable|integer|min:1',
            
            // Progress Test Skills validation
            'progress_skills'               => 'nullable|array',
            'progress_skills.*'             => 'exists:skills,id',
            'progress_grades'               => 'nullable|array',
            'progress_grades.*.skill_id'    => 'required_with:progress_grades|exists:skills,id',
            'progress_grades.*.max_grade'   => 'required_with:progress_grades|numeric|min:0',
            
            // Mid & Final Exam Skills validation
            'exam_skills'                   => 'nullable|array',
            'exam_skills.*'                 => 'exists:skills,id',
            'exam_grades'                   => 'nullable|array',
            'exam_grades.*.skill_id'        => 'required_with:exam_grades|exists:skills,id',
            'exam_grades.*.mid_max'         => 'required_with:exam_grades|numeric|min:0',
            'exam_grades.*.final_max'       => 'required_with:exam_grades|numeric|min:0',
            
            // Legacy support (للتوافق مع النماذج القديمة)
            'skills'                        => 'nullable|array',
            'skills.*'                      => 'exists:skills,id',
            'skill_grades'                  => 'nullable|array',
            'skill_grades.*.skill_id'       => 'required_with:skill_grades|exists:skills,id',
            'skill_grades.*.progress_test_max' => 'nullable|numeric|min:0',
            'skill_grades.*.mid_max'        => 'nullable|numeric|min:0',
            'skill_grades.*.final_max'      => 'nullable|numeric|min:0',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        // Create the course type from the validated request data
        $courseType = CourseType::create($request->only(['name', 'status', 'duration']));
    
        // Check if this is using the new system or legacy system
        $isNewSystem = $request->filled('progress_grades') || $request->filled('exam_grades');
        $isLegacySystem = $request->filled('skill_grades') && !$isNewSystem;
    
        if ($isLegacySystem) {
            // Handle Legacy System (النظام القديم)
            $this->handleLegacySkills($courseType, $request);
        } else {
            // Handle New System (النظام الجديد)
            $this->handleNewSkillsSystem($courseType, $request);
        }
    
        // Log the creation event
        AuditLog::create([
            'user_id'     => Auth::id(),
            'description' => 'Created a new course type: ' . $courseType->name,
            'type'        => 'create',
            'entity_id'   => $courseType->id,
            'entity_type' => CourseType::class,
        ]);
    
        return redirect()->route('admin.course-types.index')->with('success', 'Course Type created successfully.');
    }
    
    /**
     * معالجة النظام الجديد للمهارات
     */
    private function handleNewSkillsSystem(CourseType $courseType, Request $request)
    {
        // Handle Progress Test Skills
        if ($request->filled('progress_grades')) {
            $progressPivotData = [];
            foreach ($request->input('progress_grades') as $skillId => $grades) {
                $progressPivotData[$skillId] = [
                    'skill_type'        => 'progress',
                    'progress_test_max' => $grades['max_grade'],
                    'mid_max'          => null,
                    'final_max'        => null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }
            $courseType->skills()->attach($progressPivotData);
        }
    
        // Handle Mid & Final Exam Skills
        if ($request->filled('exam_grades')) {
            $examPivotData = [];
            foreach ($request->input('exam_grades') as $skillId => $grades) {
                $examPivotData[$skillId] = [
                    'skill_type'        => 'exam',
                    'progress_test_max' => null,
                    'mid_max'          => $grades['mid_max'],
                    'final_max'        => $grades['final_max'],
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }
            $courseType->skills()->attach($examPivotData);
        }
    }
    
    /**
     * معالجة النظام القديم للمهارات (للتوافق)
     */
    private function handleLegacySkills(CourseType $courseType, Request $request)
    {
        if ($request->filled('skill_grades')) {
            foreach ($request->input('skill_grades') as $skillId => $grades) {
                $progressTestMax = $grades['progress_test_max'] ?? null;
                $midMax = $grades['mid_max'] ?? null;
                $finalMax = $grades['final_max'] ?? null;
    
                // إذا كانت المهارة تحتوي على progress_test_max وأيضاً mid/final
                // نقسمها إلى سجلين منفصلين
                if ($progressTestMax && ($midMax || $finalMax)) {
                    // سجل للـ Progress Test
                    $courseType->skills()->attach($skillId, [
                        'skill_type'        => 'progress',
                        'progress_test_max' => $progressTestMax,
                        'mid_max'          => null,
                        'final_max'        => null,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
    
                    // سجل للـ Mid & Final
                    $courseType->skills()->attach($skillId, [
                        'skill_type'        => 'exam',
                        'progress_test_max' => null,
                        'mid_max'          => $midMax,
                        'final_max'        => $finalMax,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
                // إذا كانت المهارة تحتوي على progress_test_max فقط
                elseif ($progressTestMax && !$midMax && !$finalMax) {
                    $courseType->skills()->attach($skillId, [
                        'skill_type'        => 'progress',
                        'progress_test_max' => $progressTestMax,
                        'mid_max'          => null,
                        'final_max'        => null,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
                // إذا كانت المهارة تحتوي على mid/final فقط
                elseif (!$progressTestMax && ($midMax || $finalMax)) {
                    $courseType->skills()->attach($skillId, [
                        'skill_type'        => 'exam',
                        'progress_test_max' => null,
                        'mid_max'          => $midMax,
                        'final_max'        => $finalMax,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
                // إذا لم تكن هناك درجات محددة، نضعها كـ legacy
                else {
                    $courseType->skills()->attach($skillId, [
                        'skill_type'        => 'legacy',
                        'progress_test_max' => $progressTestMax,
                        'mid_max'          => $midMax,
                        'final_max'        => $finalMax,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }
        }
        // Fallback: if only skills are provided without grade details
        elseif ($request->filled('skills')) {
            foreach ($request->input('skills') as $skillId) {
                $courseType->skills()->attach($skillId, [
                    'skill_type'        => 'legacy',
                    'progress_test_max' => null,
                    'mid_max'          => null,
                    'final_max'        => null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }
    }

    /**
     * Show the form for editing the specified course type
     */
    public function edit(CourseType $courseType)
    {
        $skills = Skill::all();
        
        // جلب البيانات الحالية للعرض في النموذج
        $currentData = $this->prepareEditData($courseType);
        
        return view('admin.course_types.edit', compact('courseType', 'skills', 'currentData'));
    }

    /**
     * تحضير البيانات للعرض في نموذج التعديل
     */
    private function prepareEditData(CourseType $courseType)
    {
        $progressSkills = $courseType->progressSkills()->get();
        $examSkills = $courseType->examSkills()->get();
        $legacySkills = $courseType->legacySkills()->get();

        return [
            'progress_skills' => $progressSkills->pluck('id')->toArray(),
            'progress_grades' => $progressSkills->mapWithKeys(function ($skill) {
                return [$skill->id => [
                    'skill_id' => $skill->id,
                    'max_grade' => $skill->pivot->progress_test_max
                ]];
            })->toArray(),
            
            'exam_skills' => $examSkills->pluck('id')->toArray(),
            'exam_grades' => $examSkills->mapWithKeys(function ($skill) {
                return [$skill->id => [
                    'skill_id' => $skill->id,
                    'mid_max' => $skill->pivot->mid_max,
                    'final_max' => $skill->pivot->final_max
                ]];
            })->toArray(),
            
            'legacy_skills' => $legacySkills->pluck('id')->toArray(),
            'legacy_grades' => $legacySkills->mapWithKeys(function ($skill) {
                return [$skill->id => [
                    'skill_id' => $skill->id,
                    'progress_test_max' => $skill->pivot->progress_test_max,
                    'mid_max' => $skill->pivot->mid_max,
                    'final_max' => $skill->pivot->final_max
                ]];
            })->toArray(),
            
            'has_legacy_data' => $legacySkills->isNotEmpty()
        ];
    }

    /**
     * تحويل البيانات القديمة إلى النظام الجديد
     */
    public function migrateLegacyData(CourseType $courseType)
    {
        if ($courseType->migrateLegacyData()) {
            return redirect()->back()->with('success', 'Legacy data migrated successfully.');
        }
        
        return redirect()->back()->with('error', 'Failed to migrate legacy data.');
    }

    public function update(Request $request, CourseType $courseType)
    {
        $validator = Validator::make($request->all(), [
            'name'                          => 'required|string|max:255|unique:course_types,name,' . $courseType->id,
            'status'                        => 'required|in:active,inactive',
            'duration'                      => 'nullable|integer|min:1',
            
            // Progress Test Skills validation
            'progress_skills'               => 'nullable|array',
            'progress_skills.*'             => 'exists:skills,id',
            'progress_grades'               => 'nullable|array',
            'progress_grades.*.skill_id'    => 'required_with:progress_grades|exists:skills,id',
            'progress_grades.*.max_grade'   => 'required_with:progress_grades|numeric|min:0',
            
            // Mid & Final Exam Skills validation
            'exam_skills'                   => 'nullable|array',
            'exam_skills.*'                 => 'exists:skills,id',
            'exam_grades'                   => 'nullable|array',
            'exam_grades.*.skill_id'        => 'required_with:exam_grades|exists:skills,id',
            'exam_grades.*.mid_max'         => 'required_with:exam_grades|numeric|min:0',
            'exam_grades.*.final_max'       => 'required_with:exam_grades|numeric|min:0',
            
            // Legacy support
            'skills'                        => 'nullable|array',
            'skills.*'                      => 'exists:skills,id',
            'skill_grades'                  => 'nullable|array',
            'skill_grades.*.skill_id'       => 'required_with:skill_grades|exists:skills,id',
            'skill_grades.*.progress_test_max' => 'nullable|numeric|min:0',
            'skill_grades.*.mid_max'        => 'nullable|numeric|min:0',
            'skill_grades.*.final_max'      => 'nullable|numeric|min:0',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        // Update the course type basic information
        $courseType->update($request->only(['name', 'status', 'duration']));
    
        // Detach all existing skills relationships
        $courseType->skills()->detach();
    
        // Check if this is using the new system or legacy system
        $isNewSystem = $request->filled('progress_grades') || $request->filled('exam_grades');
        $isLegacySystem = $request->filled('skill_grades') && !$isNewSystem;
    
        if ($isLegacySystem) {
            // Handle Legacy System
            $this->handleLegacySkills($courseType, $request);
        } else {
            // Handle New System
            $this->handleNewSkillsSystem($courseType, $request);
        }
    
        // Log the update event
        AuditLog::create([
            'user_id'     => Auth::id(),
            'description' => 'Updated course type: ' . $courseType->name,
            'type'        => 'update',
            'entity_id'   => $courseType->id,
            'entity_type' => CourseType::class,
        ]);
    
        return redirect()->route('admin.course-types.index')->with('success', 'Course Type updated successfully.');
    }
    

    
    public function destroy($id)
    {
        $courseType = CourseType::findOrFail($id);

        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Deleted course type: ' . $courseType->name,
            'type' => 'delete',
            'entity_id' => $courseType->id,
            'entity_type' => CourseType::class,
        ]);

        $courseType->delete();
        return redirect()->route('admin.course-types.index')->with('success', 'Course Type deleted successfully.');
    }

    public function toggle($id)
    {
        $courseType = CourseType::findOrFail($id);
        $oldStatus = $courseType->status;
        $courseType->status = $courseType->status == 'active' ? 'inactive' : 'active';
        $courseType->save();

        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Toggled course type status: ' . $courseType->name . ' from ' . $oldStatus . ' to ' . $courseType->status,
            'type' => 'toggle_status',
            'entity_id' => $courseType->id,
            'entity_type' => CourseType::class,
        ]);

        return redirect()->route('admin.course-types.index')->with('success', 'Course Type status updated successfully.');
    }
}
