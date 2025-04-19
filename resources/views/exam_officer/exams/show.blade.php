@extends('layouts.app')
@section('title', "Exam #{$exam->id} Details")

@section('content')
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
                @if($exam->status === 'new')
                    <a href="{{ route('exam_officer.exams.edit', $exam->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit"></i> Edit Exam
                    </a>
                @endif
                <a href="{{ route('exam_officer.exams.print', $exam->id) }}" class="btn btn-outline-danger">
                    <i class="fas fa-print"></i> Print Exam
                </a>
            </div>
                
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
                                <td>{{ $exam->exam_date->format('Y-m-d') }}</td>
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
                                    @elseif($exam->status === 'overdue')
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
                $now = Carbon::now();
                $examDateTime = Carbon::parse($exam->exam_date->format('Y-m-d').' '.$exam->time);
                $isExaminer   = $exam->examiner_id === auth()->id();
                $hasStarted   = $now->gte($examDateTime);
                $canEnter     = $isExaminer && $hasStarted && ! in_array($exam->status, ['new','completed']);
            @endphp

            @if(! $canEnter)
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

            <!-- Skills & Max Grades -->
            <h5 class="mt-4">
                <i class="fas fa-star text-primary me-1"></i>
                Skills & Maximum Grades
            </h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        @foreach($exam->course->courseType->skills as $skill)
                            <th class="bg-success text-light text-center">
                                {{ ucfirst($skill->name) }}
                            </th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        @foreach($exam->course->courseType->skills as $skill)
                            <td class="text-center">
                                @if($exam->exam_type === 'pre')
                                    {{ $skill->pivot->pre_max }}
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

            <!-- Students & Grades -->
            <h5 class="mt-4">
                <i class="fas fa-user-graduate text-primary me-1"></i>
                Enrolled Students & Grades
            </h5>
            @php
                $ongoing = $exam->course->students()->wherePivot('status','ongoing')->get();
            @endphp

            <form action="{{ route('exam_officer.exams.grades.store', $exam->id) }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            @foreach($exam->course->courseType->skills as $skill)
                                <th class="text-center">
                                    {{ $skill->name }}<br>
                                    <small>
                                        @if($exam->exam_type==='pre')
                                            (Max: {{ $skill->pivot->pre_max }})
                                        @elseif($exam->exam_type==='mid')
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
                        @foreach($ongoing as $student)
                            <tr>
                                <td>{{ $student->id }}</td>
                                <td>{{ $student->name }}</td>
                                @foreach($exam->course->courseType->skills as $skill)
                                    @php
                                        $examStudent = $exam->examStudents->firstWhere('student_id',$student->id);
                                        $gradeValue  = optional($examStudent?->grades->firstWhere('course_type_skill_id',$skill->id))->grade;
                                    @endphp
                                    <td>
                                        <input
                                            type="number"
                                            step="0.01"
                                            name="grades[{{ $student->id }}][{{ $skill->id }}]"
                                            value="{{ old("grades.{$student->id}.{$skill->id}", $gradeValue) }}"
                                            class="form-control"
                                            @if(! $canEnter) disabled @endif
                                        >
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button
                        type="submit"
                        class="btn btn-primary"
                        @if(! $canEnter)
                            disabled
                        @endif
                    >
                        Save Grades
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
