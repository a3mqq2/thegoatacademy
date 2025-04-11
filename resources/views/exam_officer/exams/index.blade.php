@extends('layouts.app')
@section('title', 'Exams Table')

@section('content')
<div class="container-fluid">

    <!-- FILTERS (Collapsible) -->
    <div class="card mb-3">
        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
            <h5 class="mb-0">Filter Options</h5>
        </div>
        <div class="collapse show" id="filterCollapse">
            <div class="card-body">
                <form method="GET" action="{{ route('exam_officer.exams.index') }}">
                    <div class="row g-3">
                        <!-- Course Type -->
                        <div class="col-12 col-md-2">
                            <label for="course_type_id" class="form-label">Course Type</label>
                            <select class="form-select" name="course_type_id" id="course_type_id">
                                <option value="">All</option>
                                @foreach($courseTypes as $ct)
                                    <option value="{{ $ct->id }}"
                                        {{ request('course_type_id') == $ct->id ? 'selected' : '' }}>
                                        {{ $ct->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Group Type -->
                        <div class="col-12 col-md-2">
                            <label for="group_type_id" class="form-label">Group Type</label>
                            <select class="form-select" name="group_type_id" id="group_type_id">
                                <option value="">All</option>
                                @foreach($groupTypes as $gt)
                                    <option value="{{ $gt->id }}"
                                        {{ request('group_type_id') == $gt->id ? 'selected' : '' }}>
                                        {{ $gt->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Instructor -->
                        <div class="col-12 col-md-2">
                            <label for="instructor_id" class="form-label">Instructor</label>
                            <select class="form-select" name="instructor_id" id="instructor_id">
                                <option value="">All</option>
                                @foreach($instructors as $inst)
                                    <option value="{{ $inst->id }}"
                                        {{ request('instructor_id') == $inst->id ? 'selected' : '' }}>
                                        {{ $inst->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                  @if (auth()->user()->permissions->contains('name','Exam Manager'))
                        <!-- Examiner -->
                        <div class="col-12 col-md-2">
                            <label for="examiner_id" class="form-label">Examiner</label>
                            <select class="form-select" name="examiner_id" id="examiner_id">
                                <option value="">All</option>
                                @foreach($examiners as $ex)
                                    <option value="{{ $ex->id }}"
                                        {{ request('examiner_id') == $ex->id ? 'selected' : '' }}>
                                        {{ $ex->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                     @endif
                        <!-- Status -->
                        <div class="col-12 col-md-2">
                            <label for="status" class="form-label">Exam Status</label>
                            <select class="form-select" name="status" id="status">
                                <option value="">All</option>
                                @foreach($examStatuses as $st)
                                    <option value="{{ $st }}"
                                        {{ request('status') == $st ? 'selected' : '' }}>
                                        {{ ucfirst($st) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Exam Date Filter (daily, weekly, afterTwoDays) -->
                        <div class="col-12 col-md-2">
                            <label for="exam_date_filter" class="form-label">Exam Date Filter</label>
                            <select class="form-select" name="exam_date_filter" id="exam_date_filter">
                                <option value="">All</option>
                                <option value="daily"
                                    {{ request('exam_date_filter') == 'daily' ? 'selected' : '' }}>
                                    Daily
                                </option>
                                <option value="weekly"
                                    {{ request('exam_date_filter') == 'weekly' ? 'selected' : '' }}>
                                    Weekly
                                </option>
                                <option value="afterTwoDays"
                                    {{ request('exam_date_filter') == 'afterTwoDays' ? 'selected' : '' }}>
                                    After Two Days
                                </option>
                            </select>
                        </div>

                        <!-- Filter Button -->
                        <div class="col-12 d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                Filter
                            </button>
                        </div>
                    </div> <!-- row -->
                </form>
            </div>
        </div>
    </div> <!-- end filter card -->

    <!-- Exams Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Exams</h5>
        </div>
        <div class="card-body">
            @if($exams->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Course / Type</th>
                                <th>Instructor</th>
                                <th>Exam Type</th>
                                <th>Status</th>
                                <th>Examiner</th>
                                <th>Exam Date</th>
                                <th>Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>


                           

                            @foreach($exams as $exam)

                            @php
                                         // تجهيز تاريخ ووقت الامتحان كـ Carbon
                                         $examDateTime = null;
                                       if ($exam->exam_date && $exam->time) {
                                          $examDateTime = $exam->exam_date;
                                       }

                                       // هل تأخر لأكثر من 48 ساعة ولم يُكتمل؟
                                       $isOverdue = false;
                                       if ($examDateTime && $exam->status !== 'completed') {
                                          $isOverdue = $examDateTime->copy()->addHours(48)->isPast();
                                       }
                            @endphp

                                <tr  class="{{ $isOverdue ? 'table-danger' : '' }}" >
                                    <td>{{ $exam->id }}</td>
                                    @php 
                                        $course = $exam->course;
                                        $ctName = optional($course->courseType)->name;
                                        $gtName = optional($course->groupType)->name;
                                        $instructorName = optional($course->instructor)->name;

                                    @endphp
                                    <td  class="{{ $isOverdue ? 'table-danger' : '' }}">
                                        <strong>(#{{ $course->id }})</strong>
                                        @if($ctName) / {{ $ctName }} @endif
                                        @if($gtName) / {{ $gtName }} @endif
                                    </td>
                                    <td>{{ $instructorName ?? '-' }}</td>
                                    <td>{{ $exam->exam_type }}</td>
                                    <td>
                                        @if($exam->status == 'new')
                                            <span class="badge bg-info text-dark">New</span>
                                        @elseif($exam->status == 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($exam->status == 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($exam->status == "overdue")
                                            <span class="badge bg-danger">Overdue</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($exam->examiner)->name ?? '-' }}</td>
                                    <td>{{ optional($exam->exam_date)->format('Y-m-d') }}</td>
                                    <td>{{ $exam->time ?? '-' }}</td>
                                    <td>
                                       @php
                                           // Convert exam_date to a Carbon instance if not null
                                           $examDateObj = optional($exam->exam_date);
                                           // Check if it's today
                                           $isToday = $examDateObj->isToday() ?? false;
                               
                                           // Check if the current user is the assigned examiner
                                           $isExaminer = ($exam->examiner_id == Auth::id());
                                       @endphp
                               
                                       <!-- Logic:
                                            1) If exam is 'new'/'pending' but exam_date is NOT today => show "Prepare / Edit" 
                                            2) If exam_date is today AND the user is the assigned examiner => show "رصد الدرجات" 
                                            3) If exam_date is today but user is not examiner => do nothing
                                       -->


                                       {{-- show route --}}
                                       <a href="{{ route('exam_officer.exams.show', $exam->id) }}" class="btn btn-sm btn-info">
                                           Show <i class="fa fa-eye"></i>
                                       </a>


                                           <!-- Show Prepare / Edit only if NOT today -->
                                   
                                            @if ($exam->status == "new" && auth()->user()->permissions->contains('name','Exam Manager'))
                                            <button 
                                            type="button" 
                                            class="btn btn-sm btn-primary prepExamBtn" 
                                            data-examid="{{ $exam->id }}"
                                            data-examtime="{{ $exam->time }}"
                                            data-examdate="{{ optional($exam->exam_date)->format('Y-m-d') }}"
                                            data-examinerid="{{ $exam->examiner_id ?? '' }}"
                                            data-status="{{ $exam->status }}"
                                            data-grammarmax="{{ $exam->grammar_max ?? 30 }}"
                                            data-vocabmax="{{ $exam->vocabulary_max ?? 40 }}"
                                            data-practicalmax="{{ $exam->practical_english_max ?? 10 }}"
                                            data-readingmax="{{ $exam->reading_max ?? 15 }}"
                                            data-writingmax="{{ $exam->writing_max ?? 15 }}"
                                            data-listeningmax="{{ $exam->listening_max ?? 10 }}"
                                            data-speakingmax="{{ $exam->speaking_max ?? 20 }}"
                                        >
                                            @if($exam->status == 'new')
                                                Prepare <i class="fa fa-plus"></i>
                                            @else
                                                Edit Preparation <i class="fa fa-edit"></i>
                                            @endif
                                        </button>
                                            @endif

                                   </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="d-flex justify-content-center">
                   <img src="{{ asset('/images/empty-results.jpg') }}" width="400" alt="">
                </div>
                <p class="text-center h4">Empty Result.</p>
            @endif
        </div>
    </div> <!-- end card -->
</div>

<!-- Modal for Preparation/Editing -->
<div class="modal fade" id="prepExamModal" tabindex="-1" aria-labelledby="prepExamLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="prepExamForm" method="POST" action="{{ route('exam_officer.exams.prepare') }}">
            @csrf
            <input type="hidden" name="exam_id" id="modal_exam_id">
            <input type="hidden" name="current_status" id="modal_current_status">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="prepExamLabel">Exam Preparation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Examiner selection -->
                    <div class="mb-3">
                        <label for="modal_examiner_id" class="form-label">Examiner</label>
                        <select class="form-select" name="examiner_id" id="modal_examiner_id" required>
                            <option value="">Choose an Examiner</option>
                            @foreach($examiners as $ex)
                                <option value="{{ $ex->id }}">{{ $ex->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Time of exam -->
                    <div class="mb-3">
                        <label for="modal_exam_time" class="form-label">Exam Time</label>
                        <input type="time" class="form-control" name="time" id="modal_exam_time" required>
                    </div>

                    <!-- Date of exam -->
                    <div class="mb-3">
                        <label for="modal_exam_date" class="form-label">Exam Date</label>
                        <input type="date" class="form-control" name="exam_date" id="modal_exam_date" required>
                    </div>
                    <hr>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const prepButtons = document.querySelectorAll('.prepExamBtn');
    const examModal   = document.getElementById('prepExamModal');

    prepButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            let examId     = btn.getAttribute('data-examid');
            let examTime   = btn.getAttribute('data-examtime') || '';
            let examDate   = btn.getAttribute('data-examdate') || '';
            let examinerId = btn.getAttribute('data-examinerid') || '';
            let currStatus = btn.getAttribute('data-status') || 'new';

            // Max grades
            let grammarMax     = btn.getAttribute('data-grammarmax') || '30';
            let vocabMax       = btn.getAttribute('data-vocabmax') || '40';
            let practicalMax   = btn.getAttribute('data-practicalmax') || '10';
            let readingMax     = btn.getAttribute('data-readingmax') || '15';
            let writingMax     = btn.getAttribute('data-writingmax') || '15';
            let listeningMax   = btn.getAttribute('data-listeningmax') || '10';
            let speakingMax    = btn.getAttribute('data-speakingmax') || '20';

            // Populate modal fields
            document.getElementById('modal_exam_id').value        = examId;
            document.getElementById('modal_exam_time').value      = examTime;
            document.getElementById('modal_exam_date').value      = examDate;
            document.getElementById('modal_examiner_id').value    = examinerId;
            document.getElementById('modal_current_status').value = currStatus;

            // Show the modal (Bootstrap 5)
            let modal = new bootstrap.Modal(examModal);
            modal.show();
        });
    });
});
</script>
@endpush
