{{-- resources/views/exam_officer/exams/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Exams Table')

@push('styles')
    <style>
        .datepicker
        {
            width: 100% !important;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid">

  {{-- FILTERS --}}
  <div class="card mb-3">
    <div class="card-header" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
      <h5 class="mb-0">Filter Options</h5>
    </div>
    <div class="collapse show" id="filterCollapse">
      <div class="card-body">
        <form method="GET" action="{{ route('exam_officer.exams.index') }}">
          <div class="row g-3">
            <div class="col-md-2">
              <label class="form-label">Course Type</label>
              <select name="course_type_id" class="form-select">
                <option value="">All</option>
                @foreach($courseTypes as $ct)
                  <option value="{{ $ct->id }}" @selected(request('course_type_id')==$ct->id)>
                    {{ $ct->name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Group Type</label>
              <select name="group_type_id" class="form-select">
                <option value="">All</option>
                @foreach($groupTypes as $gt)
                  <option value="{{ $gt->id }}" @selected(request('group_type_id')==$gt->id)>
                    {{ $gt->name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Instructor</label>
              <select name="instructor_id" class="form-select">
                <option value="">All</option>
                @foreach($instructors as $inst)
                  <option value="{{ $inst->id }}" @selected(request('instructor_id')==$inst->id)>
                    {{ $inst->name }}
                  </option>
                @endforeach
              </select>
            </div>
            @if(auth()->user()->permissions->contains('name','Exam Manager'))
            <div class="col-md-2">
              <label class="form-label">Examiner</label>
              <select name="examiner_id" class="form-select">
                <option value="">All</option>
                @foreach($examiners as $ex)
                  <option value="{{ $ex->id }}" @selected(request('examiner_id')==$ex->id)>
                    {{ $ex->name }}
                  </option>
                @endforeach
              </select>
            </div>
            @endif
            <div class="col-md-2">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="">All</option>
                @foreach($examStatuses as $st)
                  <option value="{{ $st }}" @selected(request('status')==$st)>
                    {{ ucfirst($st) }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Date Filter</label>
              <select name="exam_date_filter" class="form-select">
                <option value="">All</option>
                <option value="daily"        @selected(request('exam_date_filter')=='daily')>Daily</option>
                <option value="weekly"       @selected(request('exam_date_filter')=='weekly')>Weekly</option>
                <option value="afterTwoDays" @selected(request('exam_date_filter')=='afterTwoDays')>After Two Days</option>
              </select>
            </div>
            <div class="col-12 d-flex justify-content-end mt-3">
              <button class="btn btn-primary">Filter</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h5 class="mb-0">Exams</h5></div>
    <div class="card-body">
      @if($exams->count())
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>ID</th><th>Course / Type</th><th>Instructor</th><th>Exam Type</th>
              <th>Status</th><th>Examiner</th><th>Exam Date</th><th>Time</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($exams as $exam)
              @php
                $isOverdue = $exam->status == 'overdue';
                $course = $exam->course;
              @endphp
              <tr @class(['table-danger'=>$isOverdue])>
                <td>{{ $exam->id }}</td>
                <td>
                  <strong>(#{{ $course->id }})</strong>
                  @if($course->courseType) / {{ $course->courseType->name }} @endif
                  @if($course->groupType)  / {{ $course->groupType->name  }} @endif
                </td>
                <td>{{ optional($course->instructor)->name ?? '-' }}</td>
                <td>{{ $exam->exam_type }}</td>
                <td>
                  <span @class([
                    'badge',
                    'bg-info text-dark'    => $exam->status=='new',
                    'bg-warning text-dark' => $exam->status=='assigned',
                    'bg-success'           => $exam->status=='completed',
                    'bg-danger'            => $exam->status=='overdue',
                  ])>
                    {{ ucfirst($exam->status) }}
                  </span>
                </td>
                <td>
                  <div class="d-flex align-items-center justify-content-between">
                    <span>{{ optional($exam->examiner)->name ?? '-' }}</span>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-primary ms-2"
                      data-bs-toggle="modal"
                      data-bs-target="#assignExaminerModal-{{ $exam->id }}">
                      <i class="fas fa-user-edit"></i>
                    </button>
                  </div>
                </td>
                <td>
                  <div class="d-flex align-items-center justify-content-between">
                    <span>{{ optional($exam->exam_date)->format('Y-m-d') }}</span>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-secondary ms-2"
                      data-bs-toggle="modal"
                      data-bs-target="#editDateModal-{{ $exam->id }}">
                      <i class="fas fa-calendar-alt"></i>
                    </button>
                  </div>
                </td>
                <td>{{ $exam->time ?? '-' }}</td>
                <td>
                  <a href="{{ route('exam_officer.exams.show',$exam->id) }}" class="btn btn-sm btn-info">
                    Show
                  </a>
                  @if($exam->status=='new' && auth()->user()->permissions->contains('name','Exam Manager'))
                  <button
                    type="button"
                    class="btn btn-sm btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#prepExamModal-{{ $exam->id }}">
                    Prepare
                  </button>
                  @endif
                  @if(auth()->user()->permissions->contains('name','Exam Manager'))
                  <button
                    class="btn btn-sm btn-success"
                    data-bs-toggle="modal"
                    data-bs-target="#completeExamModal-{{ $exam->id }}">
                    Mark as Complete
                  </button>
                @endif
                
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
  
      {{-- ✅ جميع المودالات هنا خارج الجدول --}}
      @foreach($exams as $exam)
        {{-- Modal: Edit Exam Date --}}
        <div class="modal fade" id="editDateModal-{{ $exam->id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <form method="POST" action="{{ route('exam_officer.exams.update_date') }}">
              @csrf
              <input type="hidden" name="exam_id" value="{{ $exam->id }}">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Edit Date - Exam #{{ $exam->id }}</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">New Exam Date</label>
                    <input type="text" name="exam_date" class="form-control datepicker"
                      value="{{ optional($exam->exam_date)->format('Y-m-d') }}" required>
                  </div>
                </div>
                <div class="modal-footer">
                  <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button class="btn btn-primary" type="submit">Save</button>
                </div>
              </div>
            </form>
          </div>
        </div>
  

        {{-- Modal: Mark as Complete --}}
        <div class="modal fade" id="completeExamModal-{{ $exam->id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <form method="POST" action="{{ route('exam_officer.exams.complete') }}">
              @csrf
              <input type="hidden" name="exam_id" value="{{ $exam->id }}">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title text-success">Mark Exam #{{ $exam->id }} as Complete</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <p>Are you sure you want to mark this exam as <strong>completed</strong>? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                  <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button class="btn btn-success" type="submit">Yes, Mark as Complete</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        {{-- Modal: Assign Examiner --}}
        <div class="modal fade" id="assignExaminerModal-{{ $exam->id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <form method="POST" action="{{ route('exam_officer.exams.assign_examiner') }}">
              @csrf
              <input type="hidden" name="exam_id" value="{{ $exam->id }}">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Assign Examiner - Exam #{{ $exam->id }}</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">Select Examiner</label>
                    <select name="examiner_id" class="form-select" required>
                      <option value="">Choose...</option>
                      @foreach($examiners as $ex)
                        <option value="{{ $ex->id }}" @selected($exam->examiner_id==$ex->id)>
                          {{ $ex->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="modal-footer">
                  <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button class="btn btn-primary" type="submit">Save</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      @endforeach
  
      @else
        <div class="d-flex justify-content-center">
          <img src="{{ asset('images/empty-results.jpg') }}" width="400" alt="">
        </div>
        <p class="text-center h4">Empty Result.</p>
      @endif
    </div>
  </div>
  
</div>

{{-- PER-EXAM PREPARE MODALS --}}
@foreach($exams as $exam)
@php
  // grab the course and split its class time
  $course    = $exam->course;
  [$courseStart, $courseEnd] = explode(' - ', $course->time);
@endphp

<div class="modal fade" id="prepExamModal-{{ $exam->id }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('exam_officer.exams.prepare') }}">
      @csrf
      <input type="hidden" name="exam_id"        value="{{ $exam->id }}">
      <input type="hidden" name="current_status" value="{{ $exam->status }}">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Prepare Exam #{{ $exam->id }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          {{-- Examiner --}}
          <div class="mb-3">
            <label class="form-label">Examiner</label>
            <select name="examiner_id" class="form-select" required>
              <option value="">Choose an Examiner</option>
              @foreach($examiners as $ex)
                <option value="{{ $ex->id }}" @selected($exam->examiner_id==$ex->id)>
                  {{ $ex->name }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Time (default to exam time or course start time) --}}
          <div class="mb-3">
            <label class="form-label">Time</label>
            <input
              type="time"
              name="time"
              class="form-control"
              value="{{ old('time', $exam->time ?? $courseStart) }}"
              required
            >
          </div>

          {{-- Date --}}
          <div class="mb-3">
            <label class="form-label">Date</label>
            <input
              type="text"
              name="exam_date"
              class="form-control datepicker"
            style="width: 100% !important;"
              value="{{ old('exam_date', optional($exam->exam_date)->format('Y-m-d')) }}"
              required
            >
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button class="btn btn-primary">Save Changes</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endforeach

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    flatpickr("input.datepicker", {
      dateFormat: "Y-m-d",
      altInput: true,
      altFormat: "F j, Y",
      allowInput: true
    });
  });
</script>
@endpush
