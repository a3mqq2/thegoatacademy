@extends('layouts.app')
@section('title', "Exam #{$exam->id} Details")

@section('content')
@php
    use Carbon\Carbon;
    $now = Carbon::now();
    $examDateTime = Carbon::parse($exam->exam_date->format('Y-m-d').' '.$exam->time);
    $isExaminer   = $exam->examiner_id == auth()->id();
    $hasStarted   = $now->gte($examDateTime);
    $hasTime      = !is_null($exam->time);
    $canEnter     = $isExaminer && $hasStarted && ! in_array($exam->status, ['new']) && $hasTime;

    // للـ Mid و Final فقط - نحصل على مهارات الامتحان فقط
    $skills = $exam->course->courseType->examSkills;
    $ongoing = $exam->course->students()->wherePivot('status','ongoing')->get();

    // فصل الطلاب الحاضرين والغائبين
    $presentStudents = $ongoing->filter(function($student) use ($exam) {
        $examStudent = $exam->examStudents->firstWhere('student_id', $student->id);
        return !$examStudent || $examStudent->status !== 'absent';
    });

    $absentStudents = $ongoing->filter(function($student) use ($exam) {
        $examStudent = $exam->examStudents->firstWhere('student_id', $student->id);
        return $examStudent && $examStudent->status === 'absent';
    });
@endphp

<div class="container-fluid mt-3">
    <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Exam #{{ $exam->id }} - {{ ucfirst($exam->exam_type) }}
            </h4>
            <div class="ms-auto">
                <a href="{{ route('exam_officer.exams.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Exams
                </a>
                <a href="{{ route('exam_officer.exams.print', $exam->id) }}" class="btn btn-outline-danger">
                    <i class="fas fa-print"></i> Print Results
                </a>
            </div>
        </div>
        <div class="card-body">

            <div class="row g-3">
                <div class="col-md-6">
                    <h5><i class="fas fa-book-open text-primary me-1"></i> Basic Exam Information</h5>
                    <table class="table table-bordered mb-3">
                        <tbody>
                            <tr>
                                <th class="bg-light text-dark">Exam Date:</th>
                                <td>{{ $exam->exam_date->format('Y-m-d') }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark">Exam Time:</th>
                                <td>{{ $exam->time ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light text-dark">Status:</th>
                                <td>
                                    @if($exam->status == 'new')
                                        <span class="badge bg-info text-dark">New</span>
                                    @elseif($exam->status == 'assigned')
                                        <span class="badge bg-warning text-dark">Assigned</span>
                                    @elseif($exam->status == 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($exam->status == 'overdue')
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
                    <h5><i class="fas fa-chalkboard-teacher text-primary me-1"></i> Course / Instructor</h5>
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

            <!-- إحصائيات الحضور والغياب -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h5><i class="fas fa-user-check me-2"></i>Present Students</h5>
                            <h3>{{ $presentStudents->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h5><i class="fas fa-user-times me-2"></i>Absent Students</h5>
                            <h3>{{ $absentStudents->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h5><i class="fas fa-users me-2"></i>Total Students</h5>
                            <h3>{{ $ongoing->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            @if(!$hasTime)
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Exam time is not set. You cannot enter grades until the exam time is specified.
                </div>
            @elseif(! $canEnter)
                <div class="alert alert-warning">
                    @unless($isExaminer)
                        You cannot enter grades because you are not the assigned examiner.
                    @else
                        @unless($hasStarted)
                            You cannot enter grades before the exam date and time.
                        @else
                            Grades cannot be entered in the current status ({{ ucfirst($exam->status) }}).
                        @endunless
                    @endunless
                </div>
            @endif

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                This is a {{ ucfirst($exam->exam_type) }} Exam using <strong>Mid & Final Exam Skills</strong> only.
            </div>

            <h5 class="mt-4"><i class="fas fa-star text-primary me-1"></i> Skills & Maximum Grades</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            @foreach($skills as $skill)
                                <th class="bg-success text-light text-center">{{ ucfirst($skill->name) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach($skills as $skill)
                                <td class="text-center">
                                    @if($exam->exam_type == 'mid')
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

            @if($absentStudents->count() > 0)
                <div class="mb-3">
                    <button type="button" class="btn btn-outline-warning" id="toggleAbsentStudents">
                        <i class="fas fa-eye me-1"></i>
                        <span id="toggleText">Show Absent Students ({{ $absentStudents->count() }})</span>
                    </button>
                </div>
            @endif

            {{-- نموذج حفظ الدرجات --}}
            <form id="gradesForm" action="{{ route('exam_officer.exams.grades.store', $exam->id) }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                @foreach($skills as $skill)
                                    @php $pivotId = $skill->pivot->id; @endphp
                                    <th class="text-center">
                                        {{ $skill->name }}<br>
                                        <small>
                                            @if($exam->exam_type == 'mid')
                                                (Max: {{ $skill->pivot->mid_max }})
                                            @else
                                                (Max: {{ $skill->pivot->final_max }})
                                            @endif
                                        </small>
                                    </th>
                                @endforeach
                                <th class="text-center">Percentage</th>
                                @if($canEnter)
                                    <th class="text-center">Actions</th>
                                @endif
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($presentStudents as $student)
                                @php
                                    $es        = $exam->examStudents->firstWhere('student_id', $student->id);
                                    $sumGrades = 0;
                                    $sumMax    = 0;
                                @endphp
                                <tr>
                                    <td>{{ $student->id }}</td>
                                    <td>{{ $student->name }}</td>

                                    @foreach($skills as $skill)
                                        @php
                                            $pivotId    = $skill->pivot->id;
                                            $gradeValue = optional(
                                                $es?->grades->firstWhere('course_type_skill_id', $pivotId)
                                            )->grade ?: 0;

                                            $maxValue   = $exam->exam_type == 'mid'
                                                ? $skill->pivot->mid_max
                                                : $skill->pivot->final_max;

                                            $sumGrades += $gradeValue;
                                            $sumMax    += $maxValue;
                                        @endphp

                                        <td>
                                            <input
                                                type="number"
                                                step="0.01"
                                                max="{{ $maxValue }}"
                                                name="grades[{{ $student->id }}][{{ $pivotId }}]"
                                                value="{{ old("grades.{$student->id}.{$pivotId}", $gradeValue) }}"
                                                class="form-control text-center"
                                                @if(! $canEnter) disabled @endif
                                            >
                                        </td>
                                    @endforeach

                                    @php
                                        $percentage = $sumMax > 0
                                            ? round($sumGrades / $sumMax * 100, 1)
                                            : 0;
                                    @endphp
                                    <td class="text-center {{ $percentage >= 50 ? 'text-success' : 'text-danger' }}">
                                        {{ $percentage }}%
                                    </td>

                                    @if($canEnter)
                                        <td class="text-center">
                                            {{-- زر تغيير الحالة لغياب باستخدام form/fo​rmaction دون تداخل نماذج --}}
                                            <button
                                                type="submit"
                                                class="btn btn-warning btn-sm"
                                                title="Mark as Absent"
                                                form="quickActionForm"
                                                formaction="{{ route('exam_officer.exams.students.absent', [$exam->id, $student->id]) }}"
                                                formmethod="POST"
                                                formnovalidate
                                                onclick="return confirm('Are you sure you want to mark {{ $student->name }} as absent?')"
                                            >
                                                <i class="fas fa-user-times"></i>
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-end">Average</th>

                                @foreach($skills as $skill)
                                    @php
                                        $pivotId = $skill->pivot->id;
                                        $avg = collect($presentStudents)->map(function($student) use ($exam, $pivotId) {
                                            return optional(
                                                $exam->examStudents
                                                     ->firstWhere('student_id', $student->id)
                                                     ?->grades
                                                     ->firstWhere('course_type_skill_id', $pivotId)
                                            )->grade ?: 0;
                                        })->avg();
                                    @endphp
                                    <th class="text-center">{{ number_format($avg, 1) }}</th>
                                @endforeach

                                @php
                                    $overallAvg = collect($presentStudents)->map(function($student) use ($skills, $exam) {
                                        $sum = $maxSum = 0;
                                        foreach($skills as $skill) {
                                            $pv  = $skill->pivot->id;
                                            $grade = optional(
                                                $exam->examStudents
                                                     ->firstWhere('student_id', $student->id)
                                                     ?->grades
                                                     ->firstWhere('course_type_skill_id', $pv)
                                            )->grade ?: 0;
                                            $max = $exam->exam_type === 'mid'
                                                ? $skill->pivot->mid_max
                                                : $skill->pivot->final_max;
                                            $sum    += $grade;
                                            $maxSum += $max;
                                        }
                                        return $maxSum > 0 ? ($sum / $maxSum * 100) : 0;
                                    })->avg();
                                @endphp
                                <th class="text-center">{{ number_format($overallAvg, 1) }}%</th>

                                @if($canEnter)
                                    <th></th>
                                @endif
                            </tr>
                        </tfoot>

                    </table>
                </div>

                <div class="mt-3">
                    <button
                        type="submit"
                        class="btn btn-primary"
                        @if(! $canEnter) disabled @endif
                    >
                        <i class="fas fa-save me-1"></i> Save Grades
                    </button>
                </div>
            </form>

            {{-- نموذج خفي للاكشنات السريعة (غياب/حضور) لتفادي تداخل النماذج --}}
            <form id="quickActionForm" method="POST" style="display:none;">
                @csrf
            </form>

            <!-- جدول الطلاب الغائبين (مخفي افتراضياً) -->
            @if($absentStudents->count() > 0)
                <div id="absentStudentsSection" style="display: none;">
                    <h5 class="mt-5 text-danger"><i class="fas fa-user-times me-1"></i> Absent Students</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle table-striped">
                            <thead class="table-danger">
                                <tr>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th class="text-center">Status</th>
                                    @if($canEnter)
                                        <th class="text-center">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($absentStudents as $student)
                                    <tr>
                                        <td>{{ $student->id }}</td>
                                        <td>{{ $student->name }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-danger">
                                                <i class="fas fa-user-times me-1"></i>Absent
                                            </span>
                                        </td>
                                        @if($canEnter)
                                            <td class="text-center">
                                                <button
                                                    type="submit"
                                                    class="btn btn-success btn-sm"
                                                    title="Mark as Present"
                                                    form="quickActionForm"
                                                    formaction="{{ route('exam_officer.exams.students.present', [$exam->id, $student->id]) }}"
                                                    formmethod="POST"
                                                    formnovalidate
                                                    onclick="return confirm('Are you sure you want to mark {{ $student->name }} as present?')"
                                                >
                                                    <i class="fas fa-user-check"></i> Mark Present
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggleAbsentStudents');
    const absentSection = document.getElementById('absentStudentsSection');
    const toggleText = document.getElementById('toggleText');

    if (toggleButton && absentSection) {
        toggleButton.addEventListener('click', function() {
            if (absentSection.style.display === 'none') {
                absentSection.style.display = 'block';
                toggleText.innerHTML = '<i class="fas fa-eye-slash me-1"></i>Hide Absent Students ({{ $absentStudents->count() }})';
                toggleButton.classList.remove('btn-outline-warning');
                toggleButton.classList.add('btn-warning');
            } else {
                absentSection.style.display = 'none';
                toggleText.innerHTML = '<i class="fas fa-eye me-1"></i>Show Absent Students ({{ $absentStudents->count() }})';
                toggleButton.classList.remove('btn-warning');
                toggleButton.classList.add('btn-outline-warning');
            }
        });
    }
});
</script>

@endsection
