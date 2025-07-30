<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ProgressTest;
use App\Models\ProgressTestStudent;
use App\Models\ProgressTestGrade;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminProgressTestController extends Controller
{
    /**
     * Store a new progress test for the course
     */
    public function store(Request $request, Course $course)
    {
        $request->validate([
            'week' => 'required|integer|min:1|max:20',
            'date' => 'required|date',
            'time' => 'required',
            'close_at' => 'required|date|after:now'
        ]);

        try {
            DB::beginTransaction();

            // Check for duplicate progress test for the same week
            $existingTest = $course->progressTests()
                ->where('week', $request->week)
                ->first();

            if ($existingTest) {
                return redirect()->back()->with('error', 'A progress test already exists for week ' . $request->week);
            }

            $progressTest = $course->progressTests()->create([
                'week' => $request->week,
                'date' => $request->date,
                'time' => $request->time,
                'close_at' => Carbon::parse($request->close_at)->format('Y-m-d H:i:s')
            ]);

            $enrolledStudents = $course->students()
                ->wherePivot('status', 'ongoing')
                ->get();

            foreach ($enrolledStudents as $student) {
                $progressTest->progressTestStudents()->create([
                    'student_id' => $student->id,
                    'course_id' => $course->id
                ]);
            }

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'description' => "Added progress test week {$request->week} to course #{$course->id}",
                'type' => 'progress_test_create',
                'entity_id' => $course->id,
                'entity_type' => Course::class,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Progress test added successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating progress test: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error creating progress test. Please try again.');
        }
    }

    /**
     * Update an existing progress test
     */
    public function update(Request $request, Course $course, ProgressTest $progressTest)
    {
        $request->validate([
            'week' => 'required|integer|min:1|max:20',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'close_at' => 'required|date'
        ]);

        try {
            DB::beginTransaction();

            // Check for duplicate progress test for the same week (excluding current test)
            $existingTest = $course->progressTests()
                ->where('week', $request->week)
                ->where('id', '!=', $progressTest->id)
                ->first();

            if ($existingTest) {
                return redirect()->back()->with('error', 'A progress test already exists for week ' . $request->week);
            }

            $oldData = $progressTest->toArray();

            $progressTest->update([
                'week' => $request->week,
                'date' => $request->date,
                'time' => $request->time,
                'close_at' => Carbon::parse($request->close_at)->format('Y-m-d H:i:s')
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'description' => "Updated progress test #{$progressTest->id} in course #{$course->id}",
                'type' => 'progress_test_update',
                'entity_id' => $course->id,
                'entity_type' => Course::class,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Progress test updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating progress test: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating progress test. Please try again.');
        }
    }

    /**
     * Delete a progress test
     */
    public function destroy(Course $course, ProgressTest $progressTest)
    {
        try {
            DB::beginTransaction();

            $progressTestData = $progressTest->toArray();
            $hasGrades = $progressTest->progressTestStudents()
                ->whereHas('grades')
                ->exists();

            if ($hasGrades) {
                return redirect()->back()->with('error', 'Cannot delete progress test with existing grades. Please remove grades first.');
            }

            // Delete associated progress test students
            $progressTest->progressTestStudents()->delete();
            
            // Delete the progress test
            $progressTest->delete();

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'description' => "Deleted progress test #{$progressTest->id} from course #{$course->id}",
                'type' => 'progress_test_delete',
                'entity_id' => $course->id,
                'entity_type' => Course::class,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Progress test deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting progress test: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error deleting progress test. Please try again.');
        }
    }

    /**
     * Show progress test with admin override capability
     */
    public function show(ProgressTest $progressTest)
    {
        try {
            $progressTest->load([
                'course.courseType.progress_skills',
                'progressTestStudents.student',
                'progressTestStudents.grades'
            ]);

            // Admin can always access, regardless of close_at time
            $isAdmin = true;

            return response()->json([
                'progressTest' => [
                    'id' => $progressTest->id,
                    'date' => $progressTest->date,
                    'week' => $progressTest->week,
                    'time' => $progressTest->time,
                    'close_at' => $progressTest->close_at,
                    'course' => [
                        'id' => $progressTest->course->id,
                        'course_type' => [
                            'progress_skills' => $progressTest->course->courseType->progress_skills ?? []
                        ]
                    ],
                    'progress_test_students' => $progressTest->progressTestStudents->map(function ($pts) {
                        return [
                            'student' => [
                                'id' => $pts->student->id,
                                'name' => $pts->student->name,
                                'phone' => $pts->student->phone
                            ],
                            'grades' => $pts->grades->map(function ($grade) {
                                return [
                                    'course_type_skill_id' => $grade->course_type_skill_id,
                                    'progress_test_grade' => $grade->progress_test_grade
                                ];
                            })
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading progress test: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load progress test'], 500);
        }
    }

    /**
     * Update progress test grades with admin override
     */
    public function updateGrades(Request $request, ProgressTest $progressTest)
    {
        $request->validate([
            'students' => 'required|array',
            'students.*.student_id' => 'required|exists:students,id',
            'students.*.scores' => 'required|array',
            'admin_override' => 'nullable|boolean'
        ]);

        try {
            DB::beginTransaction();

            $isAdminOverride = $request->boolean('admin_override');

            foreach ($request->students as $studentData) {
                $progressTestStudent = $progressTest->progressTestStudents()
                    ->where('student_id', $studentData['student_id'])
                    ->first();

                if (!$progressTestStudent) {
                    // Create if doesn't exist
                    $progressTestStudent = $progressTest->progressTestStudents()->create([
                        'student_id' => $studentData['student_id']
                    ]);
                }

                // Delete existing grades for this student
                $progressTestStudent->grades()->delete();

                // Create new grades
                foreach ($studentData['scores'] as $skillId => $score) {
                    if ($score !== null && $score !== '') {
                        $progressTestStudent->grades()->create([
                            'course_type_skill_id' => $skillId,
                            'progress_test_grade' => floatval($score)
                        ]);
                    }
                }
            }

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'description' => "Updated grades for progress test #{$progressTest->id} in course #{$progressTest->course->id}" . ($isAdminOverride ? ' (Admin Override)' : ''),
                'type' => 'progress_test_grades_update',
                'entity_id' => $progressTest->course->id,
                'entity_type' => Course::class,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Progress test scores saved successfully' . ($isAdminOverride ? ' (Admin Override)' : '')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving progress test grades: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error saving progress test scores'
            ], 500);
        }
    }
}