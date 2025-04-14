@extends('layouts.app')

@section('title', 'Course Details')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('instructor.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
  <li class="breadcrumb-item">
    <a href="{{ route('instructor.courses.index') }}">
      <i class="fa fa-book"></i> Courses
    </a>
  </li>
  <li class="breadcrumb-item active">
    <i class="fa fa-info-circle"></i> Course Details
  </li>
@endsection

@push('styles')
<style>
  :root {
    --primary-color: #6f42c1;
    --secondary-color: #007bff;
    --bg-color: #f0f2f5;
    --card-bg: #ffffff;
    --glass-bg: rgba(255, 255, 255, 0.6);
    --card-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    --transition-speed: 0.3s;
  }
  body {
    background: var(--bg-color);
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
  }
  /* Base Card Styling */
  .card {
    border: none;
    border-radius: 15px;
    box-shadow: var(--card-shadow);
    overflow: hidden;
    transition: transform var(--transition-speed) ease, box-shadow var(--transition-speed) ease;
    margin-bottom: 1.5rem;
    background: var(--card-bg);
    position: relative;
    z-index: 1;
  }
  .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
  }
  .card-header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: #fff;
    padding: 1rem;
    position: relative;
    z-index: 1;
  }
  .card-header h4,
  .card-header h5 {
    margin: 0;
    font-weight: 600;
  }
  /* Course Info Row */
  .course-info-row .text-primary {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
  }
  .course-info-row .text-secondary {
    font-size: 0.95rem;
    margin-left: 1.2rem;
  }
  /* Badge Styling */
  .badge {
    font-size: 0.85rem;
    border-radius: 12px;
    margin-right: 0.25rem;
  }
  /* Student Card Styling (Glassmorphism) */
  .student-card {
    background: var(--glass-bg);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 15px;
    padding: 1rem;
    text-align: center;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    transition: transform var(--transition-speed) ease, box-shadow var(--transition-speed) ease;
    margin-bottom: 1.5rem;
    position: relative;
    z-index: 1;
  }
  .student-card h6 {
    margin-bottom: 0.5rem;
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--primary-color);
  }
  .student-card p {
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
    color: #333;
  }
  .student-status {
    margin-top: 0.5rem;
  }
  /* Audit Card Styling */
  .audit-card {
    background: var(--card-bg);
    border: 1px solid rgba(0,0,0,0.05);
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    transition: transform var(--transition-speed) ease, box-shadow var(--transition-speed) ease;
  }
  .audit-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
  }
  .audit-card .audit-title {
    font-weight: 600;
    font-size: 1rem;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
  }
  .audit-card p {
    margin-bottom: 0.25rem;
    font-size: 0.85rem;
    color: #333;
  }
  .info-icon {
    margin-right: 0.25rem;
    color: var(--primary-color);
  }
  .btn-outline-light {
    border: 1px solid rgba(255,255,255,0.4);
    color: #fff;
  }
  .btn-outline-light:hover {
    background: rgba(255,255,255,0.3);
    color: #fff;
  }
</style>
@endpush

@section('content')
<div class="container">

  <!-- Course Overview Card -->
  <div class="card mt-3">
    <div class="card-header">
      <h4 class="mb-0 text-light">
        <i class="fa fa-info-circle"></i> Course ID #{{ $course->id }}
      </h4>
      <div>
        <a href="{{ route('instructor.courses.index', ['status' => 'ongoing']) }}" class="btn btn-outline-light btn-sm" style="background: rgba(255,255,255,0.2);">
          <i class="fa fa-arrow-left"></i> Back
        </a>
      </div>
    </div>
    <div class="card-body" style="z-index: 1;">
      <div class="row gx-5 gy-3 course-info-row">
        <!-- Course Name -->
        <div class="col-4">
          <div class="text-primary mb-1">
            <i class="fa fa-book-open info-icon"></i>Course Name:
          </div>
          <div class="text-secondary">{{ $course->courseType->name ?? 'N/A' }}</div>
        </div>
        <!-- Instructor -->
        <div class="col-4">
          <div class="text-primary mb-1">
            <i class="fa fa-user info-icon"></i>Instructor:
          </div>
          <div class="text-secondary">{{ $course->instructor->name ?? 'N/A' }}</div>
        </div>
        <!-- Levels -->
        <div class="col-4">
          <div class="text-primary mb-1">
            <i class="fa fa-layer-group info-icon"></i>Levels:
          </div>
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
        <div class="col-4">
          <div class="text-primary mb-1">
            <i class="fa fa-info-circle info-icon"></i>Status:
          </div>
          <span class="badge bg-{{ 
            $course->status === 'upcoming'  ? 'warning' : (
            $course->status === 'ongoing'   ? 'info'    : (
            $course->status === 'completed' ? 'success' : (
            $course->status === 'cancelled' ? 'danger'  : 'secondary')))
          }} px-3 py-2">
            {{ ucfirst($course->status) }}
          </span>
        </div>
      </div>
      
      <hr class="my-4" />
      
      <div class="row gx-5 gy-3 course-info-row">
        <!-- Start Date -->
        <div class="col-4">
          <div class="text-primary mb-1">
            <i class="fa fa-calendar-check info-icon"></i>Start Date:
          </div>
          <div class="text-secondary">{{ $course->start_date }}</div>
        </div>
        <!-- Mid Exam Date -->
        <div class="col-4">
          <div class="text-primary mb-1">
            <i class="fa fa-pencil-alt info-icon"></i>Mid Exam Date:
          </div>
          <div class="text-secondary">{{ $course->mid_exam_date }}</div>
        </div>
        <!-- Final Exam Date -->
        <div class="col-4">
          <div class="text-primary mb-1">
            <i class="fa fa-graduation-cap info-icon"></i>Final Exam Date:
          </div>
          <div class="text-secondary">{{ $course->final_exam_date }}</div>
        </div>
        <!-- Capacity -->
        <div class="col-4">
          <div class="text-primary mb-1">
            <i class="fa fa-users info-icon"></i>Capacity:
          </div>
          <div class="text-secondary">{{ $course->student_capacity }}</div>
        </div>
      </div>

      <hr class="my-4" />

      <div class="row gx-5 gy-3 course-info-row">
        <!-- Meeting Platform -->
        <div class="col-4">
          <div class="text-primary mb-1">
            <i class="fa fa-video info-icon"></i>Meeting Platform:
          </div>
          <div class="text-secondary">{{ $course->meetingPlatform->name ?? 'N/A' }}</div>
        </div>
        <!-- Days -->
        <div class="col-4">
          <div class="text-primary mb-1">
            <i class="fa fa-calendar-alt info-icon"></i>Days:
          </div>
          <div class="text-secondary">{{ $course->days }}</div>
        </div>
        <!-- Time -->
        <div class="col-4">
          <div class="text-primary mb-1">
            <i class="fa fa-clock info-icon"></i>Time:
          </div>
          @php
          [$start, $end] = explode(' - ', $course->time);
          $formattedStart = \Carbon\Carbon::createFromFormat('H:i', $start)->format('h:i A');
          $formattedEnd = \Carbon\Carbon::createFromFormat('H:i', $end)->format('h:i A');
          @endphp

          <div class="text-secondary">{{ $formattedStart }} - {{ $formattedEnd }}</div>
        </div>
        <!-- Whatsapp Group Link -->
        <div class="col-4">
          <div class="text-primary mb-1">
            <i class="fa fa-whatsapp info-icon"></i>Whatsapp Group:
          </div>
          @if ($course->whatsapp_group_link)
            <div>
              <a href="{{ url($course->whatsapp_group_link) }}" target="_blank" class="btn btn-success">
                <i class="fa fa-link"></i>
              </a>
            </div>
          @else 
            <span class="text-secondary">-</span>
          @endif
        </div>
      </div>
    </div>
  </div>
  <!-- End Course Overview Card -->

  <!-- New Card: Attendance Records -->
  <div class="card">
    <div class="card-header" style="z-index:1;">
      <h5 class="mb-0 text-light">
        <i class="fa fa-calendar-check info-icon text-light"></i> Attendance Records
      </h5>
    </div>
    <div class="card-body" style="z-index:1;">
      @if($course->schedules->whereNotNull('attendance_taken_at')->count())
        <div class="row">
          @foreach($course->schedules->whereNotNull('attendance_taken_at') as $schedule)
            <div class="col-12 col-md-6 col-lg-4">
              <div class="audit-card">
                <div class="d-flex justify-content-between align-items-center">
                  <div class="audit-title">
                    <i class="fa fa-calendar-alt info-icon"></i> {{ \Carbon\Carbon::parse($schedule->date)->format('Y-m-d') }}
                  </div>
                  <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#attendances-{{ $schedule->id }}" aria-expanded="false" aria-controls="attendances-{{ $schedule->id }}">
                    <i class="fa fa-eye"></i>
                  </button>
                </div>
                <p>
                  <strong>Attendance Taken At:</strong> {{ $schedule->attendance_taken_at }}
                </p>
                <div class="collapse" id="attendances-{{ $schedule->id }}">
                  <ul class="list-group list-group-flush">
                    @foreach($schedule->attendances as $attendance)
                      <li class="list-group-item">
                        <strong>{{ $attendance->student->name ?? 'Unknown Student' }}</strong>
                        - Status: {{ ucfirst($attendance->attendance) }} <br>
                        @if ($attendance->notes)
                          - Notes: {{ ucfirst($attendance->notes) }}
                        @endif
                      </li>
                    @endforeach
                  </ul>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <p class="text-muted">No attendance records available.</p>
      @endif
    </div>
  </div>
  <!-- End Attendance Records Card -->

  <!-- New Card: Progress Tests -->
  <div class="card">
    <div class="card-header" style="z-index:1;">
      <h5 class="mb-0 text-light">
        <i class="fa fa-clipboard info-icon text-light"></i> Progress Tests
      </h5>
    </div>
    <div class="card-body" style="z-index:1;">
      @if($course->progressTests->count())
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>#</th>
                <th>Test Date</th>
                <th>Student Count</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($course->progressTests as $test)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ \Carbon\Carbon::parse($test->date)->format('Y-m-d') }}</td>
                  <td>{{ $test->progressTestStudents->count() }}</td>
                  <td>
                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#progressTestModal-{{ $test->id }}">
                      <i class="fa fa-eye"></i> Details
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <p class="text-muted">No progress tests available.</p>
      @endif
    </div>
  </div>
  <!-- End Progress Tests Card -->

  <!-- Enrolled Students Section (Simple Cards) -->
  <div class="card">
    <div class="card-header" style="z-index:1;">
      <h5 class="mb-0 text-light">
        <i class="fa fa-users info-icon text-light"></i> Enrolled Students
      </h5>
    </div>
    <div class="card-body" style="z-index:1;">
      @if($course->students->count())
        <div class="row">
          @foreach($course->students as $student)
            <div class="col-12 col-md-6 col-lg-4">
              <div class="student-card">
                <h6>
                  <i class="fa fa-user info-icon"></i> {{ $student->name }}
                </h6>
                <p>
                  <i class="fa fa-phone info-icon"></i> {{ $student->phone }}
                </p>
                <p class="student-status">
                  <i class="fa fa-info-circle info-icon"></i>
                  <span class="badge bg-{{ 
                    $student->pivot->status === 'ongoing'   ? 'info' : (
                    $student->pivot->status === 'withdrawn' ? 'warning' : 'danger')
                  }}">
                    {{ ucfirst($student->pivot->status) }}
                  </span>
                </p>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <p class="text-muted">No students enrolled in this course.</p>
      @endif
    </div>
  </div>
  <!-- End Enrolled Students Section -->

  <!-- Audit Logs Section (Improved Style) -->
  <div class="card">
    <div class="card-header" style="z-index:1;">
      <h5 class="mb-0 text-light">
        <i class="fa fa-history info-icon text-light"></i> Audit Logs
      </h5>
    </div>
    <div class="card-body" style="z-index:1;">
      @if($course->logs && $course->logs->count())
        <div class="row">
          @foreach($course->logs as $idx => $log)
            <div class="col-12 col-md-6 col-lg-4">
              <div class="audit-card">
                <div class="audit-title">
                  <i class="fa fa-file-alt info-icon"></i> #{{ $idx + 1 }}
                </div>
                <p>
                  <strong>Event:</strong> {{ ucfirst($log->description) }}
                </p>
                <p>
                  <strong>Performed By:</strong> {{ $log->user->name ?? 'System' }}
                </p>
                <p>
                  <strong>Created At:</strong> {{ $log->created_at->format('Y-m-d H:i') }}
                </p>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <p class="text-muted">No audit logs found for this course.</p>
      @endif
    </div>
  </div>
</div>
<!-- End Container -->

<!-- Blade-generated Modals for Progress Tests -->
@foreach($course->progressTests as $test)
  <div class="modal fade" id="progressTestModal-{{ $test->id }}" tabindex="-1" aria-labelledby="progressTestModalLabel-{{ $test->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="progressTestModalLabel-{{ $test->id }}">
              Progress Test Details - {{ \Carbon\Carbon::parse($test->date)->format('Y-m-d') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                       <td>
                        {{ $item->score }}
                       </td>
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

<!-- Remove Student Modal -->
<div class="modal fade" id="removeStudentModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="removeStudentForm" method="POST">
      @csrf
      @method('DELETE')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fa fa-user-times info-icon"></i> Remove Student
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to remove this student from the course?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Cancel
          </button>
          <button type="submit" class="btn btn-danger">
            Remove
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Remove Student Modal: Set form action dynamically (if needed)
    document.querySelectorAll('[data-bs-target="#removeStudentModal"]').forEach(button => {
      button.addEventListener("click", function () {
        const studentId = this.dataset.studentId;
        const courseId  = this.dataset.courseId;
        document.getElementById("removeStudentForm")
                .setAttribute("action", "/admin/courses/" + courseId + "/students/" + studentId);
      });
    });
  });
</script>
@endpush
