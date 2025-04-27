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

@section('content')
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

{{-- 
        @if (in_array($course->status,['canceled','completed','cancelled','paused']))
          <a href="{{ route('admin.courses.restore', $course->id) }}"   class="btn btn-success btn-sm me-2">
            <i class="fa fa-redo"></i> Restore The Course
          </a>
        @endif --}}


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
                $course->status === 'upcoming'  ? 'warning' : (
                $course->status === 'ongoing'   ? 'info'    : (
                $course->status === 'completed' ? 'success' : (
                $course->status === 'canceled' ? 'danger'  : 'secondary')))
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
            @php
            [$start, $end] = explode(' - ', $course->time);
            $formattedStart = \Carbon\Carbon::createFromFormat('H:i', $start)->format('h:i A');
            $formattedEnd = \Carbon\Carbon::createFromFormat('H:i', $end)->format('h:i A');
          @endphp

            <div class="text-secondary">{{ $formattedStart }} - {{ $formattedEnd }}</div>
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

  <!-- Schedule Section -->
  <div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-light text-primary">
      <h5 class="mb-0">
        <i class="fa fa-calendar"></i> Schedule
      </h5>
      <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#scheduleCollapse" aria-expanded="true" aria-controls="scheduleCollapse">
        <i class="fa fa-minus"></i>
      </button>
    </div>
    <div id="scheduleCollapse" class="collapse">
      <div class="card-body">
        @if($course->schedules->count())
          @php
            $total = $course->schedules->count();
            $midPoint = ceil($total / 2);
          @endphp
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th class="text-primary">#</th>
                  <th class="text-primary">Day</th>
                  <th class="text-primary">Date</th>
                  <th class="text-primary">From Time</th>
                  <th class="text-primary">To Time</th>
                </tr>
              </thead>
              <tbody>

                @if ($course->pre_test_date)
                <tr style="background-color: #151f42; color: #fff;">
                  <td colspan="2" class="text-light">Pre exam test</td>
                  <td class="text-light">
                    {{ $course->pre_test_date }} 
                    ({{ \Carbon\Carbon::parse($course->pre_test_date)->format('l') }})
                  </td>
                  <td colspan="2"></td>
                </tr>
                @endif


                @foreach($course->schedules as $i => $schedule)
                  <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $schedule->day }}</td>
                    <td>{{ $schedule->date }}</td>
                    <td>{{ $schedule->from_time }}</td>
                    <td>{{ $schedule->to_time }}</td>
                  </tr>
                  @if($i + 1 == $midPoint)
                  
                  @if ($course->mid_exam_date)
                  <tr style="background-color: #151f42; color: #fff;">
                    <td colspan="2" class="text-light">MID exam test</td>
                    <td class="text-light">
                      {{ $course->mid_exam_date }} 
                      ({{ \Carbon\Carbon::parse($course->mid_exam_date)->format('l') }})
                    </td>
                    <td colspan="2"></td>
                  </tr>
                  @endif
                    
                  @endif
                @endforeach
                
                @if ($course->final_exam_date)
                <tr style="background-color: #151f42; color: #fff;">
                  <td colspan="2" class="text-light">Final exam test</td>
                  <td class="text-light">
                    {{ $course->final_exam_date }} 
                    ({{ \Carbon\Carbon::parse($course->final_exam_date)->format('l') }})
                  </td>
                  <td colspan="2"></td>
                </tr>
                @endif

              </tbody>
            </table>
          </div>
        @else
          <p class="text-muted mb-0">No schedule entries available for this course.</p>
        @endif
      </div>
    </div>
  </div>
  <!-- End Schedule Section -->

  <!-- Progress Tests Section -->
  <div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-light text-primary">
      <h5 class="mb-0">
        <i class="fa fa-clipboard"></i> Progress Tests
      </h5>
      <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#progressTestsCollapse" aria-expanded="true" aria-controls="progressTestsCollapse">
        <i class="fa fa-minus"></i>
      </button>
    </div>
    <div id="progressTestsCollapse" class="collapse">
      <div class="card-body">
        @if($course->progressTests->count())
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Date</th>
                  <th>Students Taken</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($course->progressTests as $i => $test)
                  <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($test->date)->format('Y-m-d') }}</td>
                    <td>{{ $test->progressTestStudents->count() }}</td>
                    <td>
                      <button class="btn btn-sm btn-info" 
                              data-bs-toggle="modal" 
                              data-bs-target="#progressTestDetailsModal-{{ $test->id }}">
                        <i class="fa fa-eye"></i> View Details
                      </button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <p class="text-muted mb-0">No progress tests available for this course.</p>
        @endif
      </div>
    </div>
  </div>
  <!-- End Progress Tests Section -->

  <!-- Enrolled Students Section -->
  <div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-light text-primary">
      <h5 class="mb-0">
        <i class="fa fa-users"></i> Enrolled Students
      </h5>
      <div class="d-flex align-items-center">
        @if($course->status !== 'completed' && $course->status !== 'cancelled')
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
                        $student->pivot->status === 'ongoing'   ? 'info' : (
                        $student->pivot->status === 'withdrawn' ? 'warning' : 'danger')
                      }}">
                        {{ ucfirst($student->pivot->status) }}
                      </span>
                    </td>
                    <td>
                      @if($student->pivot->status === 'excluded' && $student->pivot->exclude_reason_id)
                        <span class="text-danger">
                          {{ \App\Models\ExcludeReason::find($student->pivot->exclude_reason_id)?->name ?? 'N/A' }}
                        </span>
                      @elseif($student->pivot->status === 'withdrawn' && $student->pivot->withdrawn_reason_id)
                        <span class="text-warning">
                          {{ \App\Models\WithdrawnReason::find($student->pivot->withdrawn_reason_id)?->name ?? 'N/A' }}
                        </span>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                    <td>
                      @if($student->pivot->status === 'ongoing')
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
  <!-- End Enrolled Students Section -->

  <!-- Cancel Course Button -->
  @if($course->status === 'ongoing')
    <div class="mt-4 text-end">
      <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelCourseModal" data-course-id="{{ $course->id }}">
        <i class="fa fa-ban"></i> Cancel Course
      </button>
    </div>
  @endif

  <!-- Audit Logs Section -->
  <div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-light text-primary">
      <h5 class="mb-0">
        <i class="fa fa-history"></i> Audit Logs
      </h5>
      <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#auditLogsCollapse" aria-expanded="true" aria-controls="auditLogsCollapse">
        <i class="fa fa-minus"></i>
      </button>
    </div>
    <div id="auditLogsCollapse" class="collapse">
      <div class="card-body">
        @if($course->logs && $course->logs->count())
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Event</th>
                  <th>Performed By</th>
                  <th>Created At</th>
                </tr>
              </thead>
              <tbody>
                @foreach($course->logs as $idx => $log)
                  <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ ucfirst($log->description) }}</td>
                    <td>{{ $log->user->name ?? 'System' }}</td>
                    <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <p class="text-muted mb-0">No audit logs found for this course.</p>
        @endif
      </div>
    </div>
  </div>
</div>
<!-- End Container -->

<!-- Modals for Progress Test Details -->
@foreach($course->progressTests as $test)
  <div class="modal fade" id="progressTestDetailsModal-{{ $test->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Progress Test Details - {{ \Carbon\Carbon::parse($test->date)->format('Y-m-d') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          @if($test->progressTestStudents->count())
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Student Phone</th>
                    <th>Score</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($test->progressTestStudents as $item)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $item->student->name ?? 'N/A' }}</td>
                      <td>{{ $item->student->phone ?? 'N/A' }}</td>
                      <td>{{ $item->score }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <p>No student records found for this test.</p>
          @endif
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
@endforeach

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

<!-- Complete Course Modal -->
<div class="modal fade" id="completeCourseModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="completeCourseForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-check"></i> Complete Course</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to mark this course as completed?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
          <button type="submit" class="btn btn-success">Yes, Complete</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Audit Logs Modal -->
<div class="modal fade" id="auditChangeModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-eye"></i> Audit Changes</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
         <div class="row mb-3">
           <div class="col-md-6">
             <label class="form-label fw-bold">Old Values:</label>
             <pre id="logOldValues" class="bg-light p-2 rounded" style="max-height: 300px; overflow:auto;"></pre>
           </div>
           <div class="col-md-6">
             <label class="form-label fw-bold">New Values:</label>
             <pre id="logNewValues" class="bg-light p-2 rounded" style="max-height: 300px; overflow:auto;"></pre>
           </div>
         </div>
      </div>
      <div class="modal-footer">
         <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
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




<!-- Scripts for modals, Select2, and FilePond -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Cancel Course Modal
    document.querySelectorAll('[data-bs-target="#cancelCourseModal"]').forEach(button => {
      button.addEventListener("click", function () {
        const courseId = this.dataset.courseId;
        document.getElementById("cancelCourseForm").setAttribute("action", "/admin/courses/" + courseId + "/cancel");
      });
    });

    // Complete Course Modal
    document.querySelectorAll('[data-bs-target="#completeCourseModal"]').forEach(button => {
      button.addEventListener("click", function () {
        const courseId = this.dataset.courseId;
        document.getElementById("completeCourseForm").setAttribute("action", "/admin/courses/" + courseId + "/complete");
      });
    });

    // Audit Logs Modal: Show old/new values
    document.querySelectorAll('[data-bs-target="#auditChangeModal"]').forEach(button => {
      button.addEventListener("click", function () {
        const oldVals = this.dataset.logOld;
        const newVals = this.dataset.logNew;
        document.getElementById("logOldValues").textContent = JSON.stringify(JSON.parse(oldVals), null, 2);
        document.getElementById("logNewValues").textContent = JSON.stringify(JSON.parse(newVals), null, 2);
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


    
    // Initialize Select2 for skills and levels multiselects
    $('#skills').select2({
      placeholder: 'Select skills',
      allowClear: true,
      width: '100%'
    });
    $('#levels').select2({
      placeholder: 'Select levels',
      allowClear: true,
      width: '100%'
    });

    // Initialize FilePond for video file input
    const videoInputElement = document.querySelector('input[id="video"]');
    FilePond.create(videoInputElement, {
      server: {
        process: {
          url: '{{ route("upload.file") }}',
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          }
        },
        revert: {
          url: '{{ route("upload.file.revert") }}',
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          }
        }
      },
      acceptedFileTypes: ['video/*'],
      onprocessfilestart: () => {
        document.getElementById('submitBtn').disabled = true;
      },
      onprocessfile: (error, file) => {
        document.getElementById('submitBtn').disabled = false;
        if (!error) {
          let serverResponse = file.serverId;
          try {
            serverResponse = JSON.parse(file.serverId);
          } catch(e) { }
          document.getElementById('video_path').value = serverResponse.path;
        }
      },
      onprocessfileabort: () => {
        document.getElementById('submitBtn').disabled = false;
      },
      onprocessfileerror: () => {
        document.getElementById('submitBtn').disabled = false;
      }
    });
  });
</script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('[data-bs-target="#updateStatusModal"]').forEach(button => {
      button.addEventListener("click", function () {
        const courseId = this.dataset.courseId;
        document.getElementById("updateStatusForm").setAttribute("action", `/admin/courses/${courseId}/update-status`);
      });
    });
  });
</script>

@endsection
