@extends('layouts.app')
@section('title', "Exam #$exam->id Details")

{{-- تأكدي من تضمين Font Awesome في ال layout الرئيسي --}}
@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Exam #{{$exam->id}} - {{ ucfirst($exam->exam_type) }}
            </h4>
        </div>
        <div class="card-body">

            <!-- Exam & Course Info Row -->
            <div class="row g-3">
                <div class="col-md-6">
                    <h5>
                        <i class="fas fa-book-open text-primary me-1"></i>
                        Basic Exam Information
                    </h5>
                    <table class="table table-bordered mb-3">
                        <tbody>
                            <tr>
                                <th class="bg-light text-dark">Exam Date:</th>
                                <td>{{ $exam->exam_date }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark">Exam Time:</th>
                                <td>{{ $exam->time ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark">Status:</th>
                                <td>
                                    @if($exam->status === 'new')
                                        <span class="badge bg-info text-dark">New</span>
                                    @elseif($exam->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($exam->status === 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($exam->status == "overdue")
                                        <span class="badge bg-danger">Overdue</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark">Examiner:</th>
                                <td>{{ optional($exam->examiner)->name ?? 'Unassigned' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>
                        <i class="fas fa-chalkboard-teacher text-primary me-1"></i>
                        Course / Instructor
                    </h5>
                    <table class="table table-bordered mb-3">
                        <tbody>
                            <tr>
                                <th class="bg-light text-dark">Course ID:</th>
                                <td>#{{ $exam->course->id }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark">Course Type:</th>
                                <td>{{ optional($exam->course->courseType)->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark">Group Type:</th>
                                <td>{{ optional($exam->course->groupType)->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark">Instructor:</th>
                                <td>{{ optional($exam->course->instructor)->name ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            @php
            use Carbon\Carbon;
            // Current time
            $now = Carbon::now();
            // Combine the exam's date (only date part) with the exam's time.
            $examDateTime = Carbon::parse($exam->exam_date->format('Y-m-d') . ' ' . $exam->time);
            // Allow grade entry if the exam status is pending AND now >= exam dateTime,
            // OR if the authenticated user is the examiner, unless the exam status is "completed" or "new".
            $canEnterGrades = (($exam->status === 'pending' && $now->gte($examDateTime)) 
                                 || ($exam->examiner_id == auth()->id()))
                                && !in_array($exam->status, ['completed', 'new']);
        @endphp
        
            <!-- جدول: عرض المهارات مع الدرجات القصوى -->
            <h5 class="mt-4">
                <i class="fas fa-star text-primary me-1"></i>
                Skills & Maximum Grades
            </h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            @foreach($exam->course->courseType->skills as $skill)
                                <td class="bg-success text-light font-weight-bold text-center">
                                    {{ ucfirst($skill->name) }}
                                </td>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach($exam->course->courseType->skills as $skill)
                                <td class="text-center">
                                    @if($exam->exam_type === 'pre')
                                        {{ $skill->pivot->final_max }}
                                    @elseif($exam->exam_type === 'mid')
                                        {{ $skill->pivot->mid_max }}
                                    @else
                                        {{ $skill->pivot->final_max }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- جدول: عرض الطلاب والدرجات مع قيم الدرجات الحالية من ExamStudentGrade -->
            <h5 class="mt-4">
                <i class="fas fa-user-graduate text-primary me-1"></i>
                Enrolled Students & Grades
            </h5>
            @php
                // استرجاع الطلاب ذوي الحالة ongoing من العلاقة.
                $ongoingStudents = $exam->course->students()->wherePivot('status', 'ongoing')->get();
            @endphp
            <form action="{{ route('exam_officer.exams.grades.store', $exam->id) }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                @foreach($exam->course->courseType->skills as $skill)
                                    <th>
                                        {{ $skill->name }}<br>
                                        <small>
                                            @if($exam->exam_type === 'pre')
                                                (Max: {{ $skill->pivot->final_max }})
                                            @elseif($exam->exam_type === 'mid')
                                                (Max: {{ $skill->pivot->mid_max }})
                                            @else
                                                (Max: {{ $skill->pivot->final_max }})
                                            @endif
                                        </small>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ongoingStudents as $student)
                                <tr>
                                    <td>{{ $student->id }}</td>
                                    <td>{{ $student->name }}</td>
                                    @foreach($exam->course->courseType->skills as $skill)
                                        @php
                                            // محاولة إيجاد سجل ExamStudentGrade الخاص بهذا الطالب والمهارة.
                                            $examStudent = $exam->examStudents->firstWhere('student_id', $student->id);
                                            $gradeValue = null;
                                            if ($examStudent) {
                                                $gradeRecord = $examStudent->grades->firstWhere('course_type_skill_id', $skill->id);
                                                if ($gradeRecord) {
                                                    $gradeValue = $gradeRecord->grade;
                                                }
                                            }
                                        @endphp
                                        <td>
                                            <input type="number" step="0.01"
                                                   name="grades[{{ $student->id }}][{{ $skill->id }}]"
                                                   value="{{ old("grades.$student->id.$skill->id", $gradeValue) }}"
                                                   class="form-control"
                                                   @if(!$canEnterGrades) disabled @endif>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($canEnterGrades)
                    <button type="submit" class="btn btn-primary mt-3">Save Grades</button>
                @endif
            </form>
        </div> <!-- card-body -->
    </div> <!-- card -->
</div> <!-- container-fluid -->
@endsection
