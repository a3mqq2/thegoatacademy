@extends('layouts.app')

@section('title', 'Course Details')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
  <li class="breadcrumb-item">
    <a href="{{ route('admin.courses.index') }}">
      <i class="fa fa-book"></i> Courses
    </a>
  </li>
  <li class="breadcrumb-item active">
    <i class="fa fa-info-circle"></i> Course Details
  </li>
@endsection

@push('styles')
<style>
  :root{--primary:#6f42c1;--secondary:#007bff;--bg:#f0f2f5;--card:#fff;--sh:0 6px 14px rgba(0,0,0,.08)}
  body{background:var(--bg)}
  .card{border:none;border-radius:12px;box-shadow:var(--sh);margin-bottom:1.5rem}
  .card-header{background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;padding:.7rem 1rem}
  .card-header h4,.card-header h5{margin:0;font-weight:600;font-size:1.05rem}
  table{width:100%;border-collapse:collapse}
  th,td{border:1px solid #dee2e6;padding:.45rem .7rem;vertical-align:middle}
  th{background:#f8f9fa;font-size:.88rem;font-weight:600}
  .exam-row{background:#151f42;color:#fff!important}
  .exam-row td {color: #fff !important;}
  .progress-row{background:#ffc107;color:#000}
  .badge{font-size:.8rem}
  .admin-controls {
    display: flex;
    gap: 5px;
    align-items: center;
  }
  .status-indicator {
    min-width: 80px;
    text-align: center;
  }
</style>
@endpush

@section('content')
@php
  /* ---------- helper: id ➜ name (Sat first) ---------- */
  $dayName = [6=>'Sat',0=>'Sun',1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri'];
@endphp

<div class="container">

  <!-- Course Overview Card -->
  <div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center bg-light text-white">
      <div>
        <h4 class="mb-0 text-primary">
          <i class="fa fa-info-circle"></i> Course ID  #{{ $course->id }}
        </h4>
      </div>
      <div class="d-flex align-items-center">
        <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-light btn-sm me-2">
          <i class="fa fa-edit"></i> Edit
        </a>

        <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-dark btn-sm me-2">
          <i class="fa fa-arrow-left"></i> Back
        </a>
        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#courseOverviewCollapse" aria-expanded="true" aria-controls="courseOverviewCollapse">
          <i class="fa fa-minus"></i>
        </button>
      </div>
    </div>
    <div id="courseOverviewCollapse" class="collapse show">
      <div class="card-body">
        <div class="row gx-5 gy-3 align-items-start">
          <!-- Course Name -->
          <div class="col-md-3">
            <div class="text-uppercase fw-semibold text-primary mb-1">Course Name:</div>
            <div class="text-secondary">{{ $course->courseType->name ?? 'N/A' }}</div>
          </div>
          <!-- Instructor -->
          <div class="col-md-3">
            <div class="text-uppercase fw-semibold text-primary mb-1">Instructor:</div>
            <div class="text-secondary">{{ $course->instructor->name ?? 'N/A' }}</div>
          </div>
          <!-- Levels -->
          <div class="col-md-3">
            <div class="text-uppercase fw-semibold text-primary mb-1">Levels:</div>
            <div class="text-secondary">
              @if($course->levels && $course->levels->count())
                @foreach($course->levels as $level)
                  {{ $level->name }}@if(!$loop->last), @endif
                @endforeach
              @else
                N/A
              @endif
            </div>
          </div>
          <!-- Status -->
          <div class="col-md-3 d-flex flex-column justify-content-start">
            <div class="text-uppercase fw-semibold text-primary mb-1">Status:</div>
            <span 
              class="align-self-start badge rounded-pill bg-{{ 
                $course->status == 'upcoming'  ? 'warning' : (
                $course->status == 'ongoing'   ? 'info'    : (
                $course->status == 'completed' ? 'success' : (
                $course->status == 'canceled' ? 'danger'  : 'secondary')))
              }} px-3 py-2"
              style="cursor: pointer;"
              data-bs-toggle="modal" 
              data-bs-target="#updateStatusModal"
              data-course-id="{{ $course->id }}"
            >
              {{ ucfirst($course->status) }}
            </span>
          </div>
        </div>
      
        <hr class="my-4" />
      
        <div class="row gx-5 gy-3 align-items-start">
          <!-- Start Date -->
          <div class="col-md-3">
            <div class="text-uppercase fw-semibold text-primary mb-1">Start Date:</div>
            <div class="text-secondary">{{ $course->start_date }}</div>
          </div>
          <!-- Mid Exam Date -->
          <div class="col-md-3">
            <div class="text-uppercase fw-semibold text-primary mb-1">Mid Exam Date:</div>
            <div class="text-secondary">{{ $course->mid_exam_date }}</div>
          </div>
          <!-- Final Exam Date -->
          <div class="col-md-3">
            <div class="text-uppercase fw-semibold text-primary mb-1">Final Exam Date:</div>
            <div class="text-secondary">{{ $course->final_exam_date }}</div>
          </div>
          <!-- Capacity -->
          <div class="col-md-3">
            <div class="text-uppercase fw-semibold text-primary mb-1">Capacity:</div>
            <div class="text-secondary">{{ $course->student_capacity }}</div>
          </div>
        </div>

        <hr class="my-4" />
        <div class="row gx-5 gy-3 align-items-start">
          <!-- Meeting Platform -->
          <div class="col-md-3">
            <div class="text-uppercase fw-semibold text-primary mb-1">Meeting Platform:</div>
            <div class="text-secondary">{{ $course->meetingPlatform->name ?? 'N/A' }}</div>
          </div>
          <!-- Days -->
          <div class="col-md-3">
            <div class="text-uppercase fw-semibold text-primary mb-1">Days:</div>
            <div class="text-secondary">{{ $course->days }}</div>
          </div>
          <!-- Time -->
          <div class="col-md-3">
            <div class="text-uppercase fw-semibold text-primary mb-1">Time:</div>
            <div class="text-secondary">{{ date('h:i A', strtotime($course->schedules()->first()->from_time) ) }} - {{ date('h:i A', strtotime($course->schedules()->first()->to_time) ) }}</div>
          </div>
          <!-- Whatsapp Group Link -->
          <div class="col-md-3">
            <div class="text-uppercase fw-semibold text-primary mb-1">Whatsapp Group Link:</div>
            @if ($course->whatsapp_group_link)
              <div class="text-secondary">
                <a href="{{ url($course->whatsapp_group_link) }}" target="_blank" class="btn btn-success">
                  <i class="fa fa-link"></i>
                </a>
              </div>
            @else 
              -
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- End Course Overview Card -->

  <!-- Enhanced Schedule Section with Admin Controls -->
  <div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="text-light"><i class="fa fa-calendar"></i> Schedule & Progress Tests</h5>
      <div class="d-flex gap-2">
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addProgressTestModal">
          <i class="fa fa-plus"></i> Add Progress Test
        </button>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
          <i class="fa fa-plus"></i> Add Schedule
        </button>
      </div>
    </div>
    <div class="card-body p-0">
      @if($course->schedules->count())
        @php
          $timeline = collect();
          foreach ($course->schedules as $idx => $s) {
            $timeline->push([
              'type'     => 'lecture',
              'no'       => $idx + 1,
              'day'      => $dayName[$s->day] ?? $s->day,
              'date'     => $s->date,
              'from'     => $s->from_time,
              'to'       => $s->to_time,
              'schedule' => $s,
              'close_at' => $s->close_at
            ]);
          }
          foreach ($course->progressTests as $pt) {
            $timeline->push([
              'type' => 'progress',
              'pt'   => $pt,
              'week' => $pt->week,
              'day'  => \Carbon\Carbon::parse($pt->date)->format('l'),
              'date' => $pt->date,
              'time' => $pt->time,
              'id'   => $pt->id,
            ]);
          }
          $timeline   = $timeline->sortBy('date')->values();
          $lecCounter = 0;
        @endphp
  
        <div class="table-responsive">
          <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Day</th>
                <th>Date</th>
                <th>From</th>
                <th>To</th>
                <th>Close At</th>
                <th class="text-center">Status</th>
                <th class="text-center">Admin Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($timeline as $row)
                {{-- Progress Test --}}
                @if($row['type'] === 'progress')
                @php
                $pt        = $row['pt'];
                $hasGrades = $pt->progressTestStudents->pluck('grades')->flatten()->isNotEmpty();
                $closed    = now()->gt(\Carbon\Carbon::parse($pt->close_at));
                @endphp
                <tr class="progress-row text-center text-dark">
                    <td colspan="1">Progress Test – Week {{ $row['week'] }}</td>
                    <td>{{ $row['date'] }} ({{ $row['day'] }})</td>
                    <td colspan="4">{{ date('h:i A', strtotime($row['time'])) }}</td>
                    <td class="status-indicator">
                        @if($hasGrades)
                            <span class="badge bg-success">
                              <i class="fa fa-check"></i> Completed
                            </span>
                        @elseif($closed)
                            <span class="badge bg-danger">
                              <i class="fa fa-times"></i> Closed
                            </span>
                        @else
                            <span class="badge bg-warning">
                              <i class="fa fa-clock"></i> Pending
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="admin-controls">
                            {{-- Edit Progress Test --}}
                            <button class="btn btn-warning btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editProgressTestModal"
                                    data-pt-id="{{ $pt->id }}"
                                    data-pt-week="{{ $pt->week }}"
                                    data-pt-date="{{ $pt->date }}"
                                    data-pt-time="{{ $pt->time }}"
                                    data-pt-close="{{ $pt->close_at }}">
                                <i class="fa fa-edit"></i>
                            </button>
                            
                            {{-- Grades Button (with admin override) --}}
                            @if($hasGrades || $closed)
                                <a href="{{ route('admin.courses.progress_tests.show', [$row['id'], 'admin' => true]) }}" 
                                   class="btn btn-info btn-sm">
                                    <i class="fa fa-chart-bar"></i> Grades
                                </a>
                            @else
                                <a href="{{ route('admin.courses.progress_tests.show', [$row['id'], 'admin' => true]) }}" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus"></i> Enter Grades
                                </a>
                            @endif

                            {{-- Download Results --}}
                            @if($hasGrades)
                                <a href="{{ route('admin.courses.progress_tests.print', $row['id']) }}" 
                                   class="btn btn-danger btn-sm">
                                    <i class="fa fa-download"></i>
                                </a>
                            @endif

                            {{-- Delete Progress Test --}}
                            <button class="btn btn-outline-danger btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteProgressTestModal"
                                    data-pt-id="{{ $pt->id }}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @else
                  {{-- Lecture Schedule --}}
                  @php
                    $lecCounter++;
                    $sch = $row['schedule'];
                    $closeAt = \Carbon\Carbon::parse($sch->close_at);
                    $today = now()->toDateString();
                    $isToday = ($row['date'] === $today);
                    $canTakeAttendance = $isToday && now()->lt($closeAt);
                  @endphp
                  <tr>
                    <td>{{ $lecCounter }}</td>
                    <td>
                      {{ $row['day'] }} 
                      @if ($sch->extra_date)
                        <div class="badge badge-info m-1 bg-info">EXTRA</div>
                      @endif
                    </td>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($row['from'])->format('g:i A') }}</td>
                    <td>{{ \Carbon\Carbon::parse($row['to'])->format('g:i A') }}</td>
                    <td>
                      {{$sch->close_at}}
                    </td>
                    <td class="text-center status-indicator">
                      @if ($sch->status == "pending")
                          <span class="badge bg-warning">
                            <i class="fa fa-hourglass-end"></i> Pending
                          </span>
                      @elseif ($sch->status == "done")
                          <span class="badge bg-success">
                            <i class="fa fa-check"></i> Done
                          </span>
                      @elseif ($sch->status == "absent")
                          <span class="badge bg-danger">
                            <i class="fa fa-times"></i> Absent
                          </span>
                      @endif
                    </td>
                    <td class="text-center">
                      <div class="admin-controls">
                        {{-- Edit Schedule --}}
                        <button class="btn btn-warning btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editScheduleModal"
                                data-schedule-id="{{ $sch->id }}"
                                data-schedule-date="{{ $sch->date }}"
                                data-schedule-from="{{ $sch->from_time }}"
                                data-schedule-to="{{ $sch->to_time }}"
                                data-schedule-day="{{ $sch->day }}"
                                data-schedule-extra="{{ $sch->extra_date ? 1 : 0 }}">
                            <i class="fa fa-edit"></i>
                        </button>

                        {{-- Status Control --}}
                        {{-- <div class="btn-group" role="group">
                          <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                  type="button" 
                                  data-bs-toggle="dropdown">
                            Status
                          </button>
                          <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="updateScheduleStatus({{ $sch->id }}, 'pending')">
                              <i class="fa fa-hourglass-end text-warning"></i> Pending
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="updateScheduleStatus({{ $sch->id }}, 'done')">
                              <i class="fa fa-check text-success"></i> Done
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="updateScheduleStatus({{ $sch->id }}, 'absent')">
                              <i class="fa fa-times text-danger"></i> Absent
                            </a></li>
                          </ul>
                        </div> --}}

                        {{-- Attendance Control --}}
                        @if($sch->attendance_taken_at)
                          <a href="{{ route('admin.courses.take_attendance', ['course' => $course->id, 'CourseSchedule' => $sch->id, 'admin' => true]) }}" 
                             class="btn btn-info btn-sm">
                            <i class="fa fa-users"></i> View
                          </a>
                        @elseif($canTakeAttendance || true) {{-- Admin can always take attendance --}}
                          <a href="{{ route('admin.courses.take_attendance', ['course' => $course->id, 'CourseSchedule' => $sch->id, 'admin' => true]) }}" 
                             class="btn btn-primary btn-sm">
                            <i class="fa fa-user-check"></i> Attendance
                          </a>
                        @endif

                        {{-- Delete Schedule --}}
                        <button class="btn btn-outline-danger btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteScheduleModal"
                                data-schedule-id="{{ $sch->id }}">
                            <i class="fa fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                @endif
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <p class="p-3 mb-0 text-muted">No schedule entries.</p>
      @endif
    </div>
  </div>

  <!-- Enrolled Students Section -->
  <div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-light text-primary">
      <h5 class="mb-0">
        <i class="fa fa-users"></i> Enrolled Students
      </h5>
      <div class="d-flex align-items-center">
        @if($course->status != 'completed' && $course->status != 'cancelled')
          <a href="#" class="btn btn-dark btn-sm me-2" data-bs-toggle="modal" data-bs-target="#enrollStudentModal">
            <i class="fa fa-user-plus"></i> Enroll Student
          </a>
        @endif
        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#enrolledStudentsCollapse" aria-expanded="true" aria-controls="enrolledStudentsCollapse">
          <i class="fa fa-minus"></i>
        </button>
      </div>
    </div>
    <div id="enrolledStudentsCollapse" class="collapse">
      <div class="card-body">
        @if($course->students->count())
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Phone</th>
                  <th>Books Due</th>
                  <th>Status</th>
                  <th>Reason</th>  
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($course->students as $i => $student)
                  <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $student->name }}</td>
                    <td>{{ $student->phone }}</td>
                    <td>
                      <span class="badge bg-{{ $student->books_due ? 'danger' : 'success' }}">
                        {{ $student->books_due ? 'Due' : 'Cleared' }}
                      </span>
                    </td>
                    <td>
                      <span class="badge bg-{{ 
                        $student->pivot->status == 'ongoing'   ? 'info' : (
                        $student->pivot->status == 'withdrawn' ? 'warning' : 'danger')
                      }}">
                        {{ ucfirst($student->pivot->status) }}
                      </span>
                    </td>
                    <td>
                      @if($student->pivot->status == 'excluded' && $student->pivot->exclude_reason_id)
                        <span class="text-danger">
                          {{ \App\Models\ExcludeReason::find($student->pivot->exclude_reason_id)?->name ?? 'N/A' }}
                        </span>
                      @elseif($student->pivot->status == 'withdrawn' && $student->pivot->withdrawn_reason_id)
                        <span class="text-warning">
                          {{ \App\Models\WithdrawnReason::find($student->pivot->withdrawn_reason_id)?->name ?? 'N/A' }}
                        </span>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                    <td>
                      @if($student->pivot->status == 'ongoing')
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#excludeStudentModal" data-student-id="{{ $student->id }}" data-course-id="{{ $course->id }}">
                          <i class="fa fa-user-slash"></i> Exclude
                        </button>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#withdrawStudentModal" data-student-id="{{ $student->id }}" data-course-id="{{ $course->id }}">
                          <i class="fa fa-user-minus"></i> Withdraw
                        </button>
                      @else
                        <span class="text-muted">No actions</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <p class="text-muted mb-0">No students enrolled in this course.</p>
        @endif
      </div>
    </div>
  </div>

  <!-- Cancel Course Button -->
  @if($course->status == 'ongoing')
    <div class="mt-4 text-end">
      <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelCourseModal" data-course-id="{{ $course->id }}">
        <i class="fa fa-ban"></i> Cancel Course
      </button>
    </div>
  @endif
</div>

<!-- Add Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('admin.courses.schedules.store', $course) }}">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-plus"></i> Add New Schedule</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Date</label>
              <input type="date" name="date" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Day</label>
              <select name="day" class="form-select" required>
                <option value="">Select Day</option>
                <option value="0">Sunday</option>
                <option value="1">Monday</option>
                <option value="2">Tuesday</option>
                <option value="3">Wednesday</option>
                <option value="4">Thursday</option>
                <option value="5">Friday</option>
                <option value="6">Saturday</option>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">From Time</label>
              <input type="time" name="from_time" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">To Time</label>
              <input type="time" name="to_time" class="form-control" required>
            </div>
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="extra_date" id="extraDate" value="1">
              <label class="form-check-label" for="extraDate">
                Mark as Extra Class
              </label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Schedule</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Edit Schedule Modal -->
<div class="modal fade" id="editScheduleModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="editScheduleForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-edit"></i> Edit Schedule</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Date</label>
              <input type="date" name="date" id="editScheduleDate" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Day</label>
              <select name="day" id="editScheduleDay" class="form-select" required>
                <option value="">Select Day</option>
                <option value="0">Sunday</option>
                <option value="1">Monday</option>
                <option value="2">Tuesday</option>
                <option value="3">Wednesday</option>
                <option value="4">Thursday</option>
                <option value="5">Friday</option>
                <option value="6">Saturday</option>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">From Time</label>
              <input type="time" name="from_time" id="editScheduleFrom" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">To Time</label>
              <input type="time" name="to_time" id="editScheduleTo" class="form-control" required>
            </div>
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="extra_date" id="editExtraDate" value="1">
              <label class="form-check-label" for="editExtraDate">
                Mark as Extra Class
              </label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">Update Schedule</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Delete Schedule Modal -->
<div class="modal fade" id="deleteScheduleModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="deleteScheduleForm" method="POST">
      @csrf
      @method('DELETE')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-trash"></i> Delete Schedule</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this schedule? This action cannot be undone.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Delete Schedule</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Add Progress Test Modal -->
<div class="modal fade" id="addProgressTestModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('admin.courses.progress_tests.store', $course) }}">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-plus"></i> Add Progress Test</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Week Number</label>
              <input type="number" name="week" class="form-control" min="1" max="20" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Date</label>
              <input type="date" name="date" class="form-control" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Time</label>
              <input type="time" name="time" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Close At</label>
              <input type="datetime-local" name="close_at" class="form-control" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Add Progress Test</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Edit Progress Test Modal -->
<div class="modal fade" id="editProgressTestModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="editProgressTestForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-edit"></i> Edit Progress Test</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Week Number</label>
              <input type="number" name="week" id="editPtWeek" class="form-control" min="1" max="20" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Date</label>
              <input type="date" name="date" id="editPtDate" class="form-control" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Time</label>
              <input type="time" name="time" id="editPtTime" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Close At</label>
              <input type="datetime-local" name="close_at" id="editPtClose" class="form-control" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">Update Progress Test</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Delete Progress Test Modal -->
<div class="modal fade" id="deleteProgressTestModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="deleteProgressTestForm" method="POST">
      @csrf
      @method('DELETE')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-trash"></i> Delete Progress Test</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this progress test? This action cannot be undone and will remove all associated grades.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Delete Progress Test</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Existing Modals from Original Code -->
<!-- Enroll Student Modal -->
<div class="modal fade" id="enrollStudentModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="enrollStudentForm" method="POST" action="{{ route('admin.courses.enroll', $course) }}">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-user-plus"></i> Enroll New Student</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Select Student</label>
            <select name="student_id" class="form-select select2" required>
              <option value="">-- Select Student --</option>
              @foreach($students as $student)
                <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->phone }})</option>
              @endforeach
            </select>
          </div>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-user-plus"></i> Enroll Student
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Exclude Student Modal -->
<div class="modal fade" id="excludeStudentModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="excludeStudentForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-user-slash"></i> Exclude Student</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Choose a reason for excluding this student:</p>
          <div class="mb-3">
            <label class="form-label"><i class="fa fa-question-circle"></i> Reason</label>
            <select name="exclude_reason_id" class="form-select" required>
              <option value="">-- Select Reason --</option>
              @foreach($excludeReasons as $reason)
                <option value="{{ $reason->id }}">{{ $reason->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">
            <i class="fa fa-user-slash"></i> Confirm Exclude
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Withdraw Student Modal -->
<div class="modal fade" id="withdrawStudentModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="withdrawStudentForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-user-minus"></i> Withdraw Student</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Choose a reason for withdrawing this student:</p>
          <div class="mb-3">
            <label class="form-label"><i class="fa fa-question-circle"></i> Reason</label>
            <select name="withdrawn_reason_id" class="form-select" required>
              <option value="">-- Select Reason --</option>
              @foreach($withdrawnReasons as $reason)
                <option value="{{ $reason->id }}">{{ $reason->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="fa fa-user-minus"></i> Confirm Withdraw
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Cancel Course Modal -->
<div class="modal fade" id="cancelCourseModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="cancelCourseForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-ban"></i> Cancel Course</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to cancel this course? This action cannot be undone.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
          <button type="submit" class="btn btn-danger">Yes, Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Update Course Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="updateStatusForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-sync"></i> Update Course Status</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Select New Status:</label>
            <select name="status" class="form-select" required>
              <option value="">-- Choose Status --</option>
              
              @php
                $today = now()->toDateString();
              @endphp

              @if ($course->start_date <= $today && $course->final_exam_date >= $today)
              <option value="ongoing">Ongoing</option>
                @elseif($course->start_date > $today)
                <option value="upcoming">Upcoming</option>
              @endif

              <option value="paused">Paused</option>
              <option value="canceled">Canceled</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Status</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  // Edit Schedule Modal
  document.querySelectorAll('[data-bs-target="#editScheduleModal"]').forEach(button => {
    button.addEventListener("click", function () {
      const scheduleId = this.dataset.scheduleId;
      const date = this.dataset.scheduleDate;
      const from = this.dataset.scheduleFrom;
      const to = this.dataset.scheduleTo;
      const day = this.dataset.scheduleDay;
      const extra = this.dataset.scheduleExtra;

      document.getElementById("editScheduleForm").setAttribute("action", `/admin/courses/{{ $course->id }}/schedules/${scheduleId}`);
      document.getElementById("editScheduleDate").value = date;
      document.getElementById("editScheduleFrom").value = from;
      document.getElementById("editScheduleTo").value = to;
      document.getElementById("editScheduleDay").value = day;
      document.getElementById("editExtraDate").checked = extra == "1";
    });
  });

  // Delete Schedule Modal
  document.querySelectorAll('[data-bs-target="#deleteScheduleModal"]').forEach(button => {
    button.addEventListener("click", function () {
      const scheduleId = this.dataset.scheduleId;
      document.getElementById("deleteScheduleForm").setAttribute("action", `/admin/courses/{{ $course->id }}/schedules/${scheduleId}`);
    });
  });

  // Edit Progress Test Modal
  document.querySelectorAll('[data-bs-target="#editProgressTestModal"]').forEach(button => {
    button.addEventListener("click", function () {
      const ptId = this.dataset.ptId;
      const week = this.dataset.ptWeek;
      const date = this.dataset.ptDate;
      const time = this.dataset.ptTime;
      const close = this.dataset.ptClose;

      document.getElementById("editProgressTestForm").setAttribute("action", `/admin/courses/{{ $course->id }}/progress-tests/${ptId}`);
      document.getElementById("editPtWeek").value = week;
      document.getElementById("editPtDate").value = date;
      document.getElementById("editPtTime").value = time;
      
      // Convert close_at to datetime-local format
      if (close) {
        const closeDate = new Date(close.replace(' ', 'T'));
        document.getElementById("editPtClose").value = closeDate.toISOString().slice(0, 16);
      }
    });
  });

  // Delete Progress Test Modal
  document.querySelectorAll('[data-bs-target="#deleteProgressTestModal"]').forEach(button => {
    button.addEventListener("click", function () {
      const ptId = this.dataset.ptId;
      document.getElementById("deleteProgressTestForm").setAttribute("action", `/admin/courses/{{ $course->id }}/progress-tests/${ptId}`);
    });
  });

  // Cancel Course Modal
  document.querySelectorAll('[data-bs-target="#cancelCourseModal"]').forEach(button => {
    button.addEventListener("click", function () {
      const courseId = this.dataset.courseId;
      document.getElementById("cancelCourseForm").setAttribute("action", "/admin/courses/" + courseId + "/cancel");
    });
  });

  // Update Status Modal
  document.querySelectorAll('[data-bs-target="#updateStatusModal"]').forEach(button => {
    button.addEventListener("click", function () {
      const courseId = this.dataset.courseId;
      document.getElementById("updateStatusForm").setAttribute("action", `/admin/courses/${courseId}/update-status`);
    });
  });

  // Exclude Student Modal
  document.querySelectorAll('[data-bs-target="#excludeStudentModal"]').forEach(btn => {
    btn.addEventListener("click", function () {
      const courseId  = this.dataset.courseId;
      const studentId = this.dataset.studentId;
      document.getElementById("excludeStudentForm").setAttribute("action", `/admin/courses/${courseId}/students/${studentId}/exclude`);
    });
  });

  // Withdraw Student Modal
  document.querySelectorAll('[data-bs-target="#withdrawStudentModal"]').forEach(btn => {
    btn.addEventListener("click", function () {
      const courseId  = this.dataset.courseId;
      const studentId = this.dataset.studentId;
      document.getElementById("withdrawStudentForm").setAttribute("action", `/admin/courses/${courseId}/students/${studentId}/withdraw`);
    });
  });
});

// Function to update schedule status via AJAX
function updateScheduleStatus(scheduleId, status) {
  if (confirm(`Are you sure you want to change the schedule status to "${status}"?`)) {
    fetch(`/admin/courses/{{ $course->id }}/schedules/${scheduleId}/status`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
      location.reload(); // Reload to show updated status
    });
  }
}
</script>

@endsection