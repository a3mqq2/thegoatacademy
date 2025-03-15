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
  <div class="card  mt-3">
    <div class="card-header d-flex justify-content-between align-items-center bg-light text-white">
      <h4 class="mb-0 text-primary">
        <i class="fa fa-info-circle"></i> Course ID  #{{ $course->id }}
      </h4>
      <div>
        <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn btn-light btn-sm me-2">
          <i class="fa fa-edit"></i> Edit
        </a>
        <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-dark btn-sm">
          <i class="fa fa-arrow-left"></i> Back
        </a>
      </div>
    </div>

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
        <!-- Mid Exam Date -->
        <div class="col-md-3">
          <div class="text-uppercase fw-semibold text-primary mb-1">Mid Exam Date:</div>
          <div class="text-secondary">{{ $course->mid_exam_date }}</div>
        </div>
        <!-- Status -->
        <div class="col-md-3 d-flex flex-column justify-content-start">
          <div class="text-uppercase fw-semibold text-primary mb-1">Status:</div>
          <span class="align-self-start badge rounded-pill bg-{{ 
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
    
      <div class="row gx-5 gy-3 align-items-start">
        <!-- Start Date -->
        <div class="col-md-3">
          <div class="text-uppercase fw-semibold text-primary mb-1">Start Date:</div>
          <div class="text-secondary">{{ $course->start_date }}</div>
        </div>
        <!-- Final Exam Date -->
        <div class="col-md-3">
          <div class="text-uppercase fw-semibold text-primary mb-1">Final Exam Date:</div>
          <div class="text-secondary">{{ $course->final_exam_date }}</div>
        </div>
        <!-- End Date -->
        <div class="col-md-3">
          <div class="text-uppercase fw-semibold text-primary mb-1">End Date:</div>
          <div class="text-secondary">{{ $course->end_date }}</div>
        </div>
        <!-- Capacity -->
        <div class="col-md-3">
          <div class="text-uppercase fw-semibold text-primary mb-1">Capacity:</div>
          <div class="text-secondary">{{ $course->student_capacity }}</div>
        </div>
      </div>


      <hr class="my-4" />
      <div class="row gx-5 gy-3 align-items-start">
        <!-- Start Date -->
        <div class="col-md-3">
          <div class="text-uppercase fw-semibold text-primary mb-1">  Meeting Platform:</div>
          <div class="text-secondary">{{ $course->meetingPlatform->name ?? 'N\A' }}</div>
        </div>
        <!-- Final Exam Date -->
        <div class="col-md-3">
          <div class="text-uppercase fw-semibold text-primary mb-1">  Days :</div>
          <div class="text-secondary">{{ $course->days }}</div>
        </div>
        <!-- End Date -->
        <div class="col-md-3">
          <div class="text-uppercase fw-semibold text-primary mb-1">  Time :</div>
          <div class="text-secondary">{{ $course->time }}</div>
        </div>
        <div class="col-md-3">
          <div class="text-uppercase fw-semibold text-primary mb-1">  Whatsapp Group Link :</div>
          @if ($course->whatsapp_group_link)
            <div class="text-secondary">
              <a href="{{url($course->whatsapp_group_link)}}" target="_blank" class="btn btn-success"><i class="fa fa-link"></i></a>
            </div>
            @else 
            -
          @endif
        </div>
      </div>

    </div>
    
    
  </div>

  <!-- Schedule Section -->
  <div class="card mt-4 ">
    <div class="card-header bg-light text-primary">
      <h5 class="mb-0 text-primary">
        <i class="fa fa-calendar"></i> Schedule
      </h5>
    </div>
    <div class="card-body">
      @if($course->schedules->count())
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
              @foreach($course->schedules as $i => $schedule)
                <tr>
                  <td>{{ $i + 1 }}</td>
                  <td>{{ $schedule->day }}</td>
                  <td>{{ $schedule->date }}</td>
                  <td>{{ $schedule->from_time }}</td>
                  <td>{{ $schedule->to_time }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <p class="text-muted mb-0">No schedule entries available for this course.</p>
      @endif
    </div>
  </div>

<div class="card mt-4">
   <div class="card-header bg-light text-primary d-flex justify-content-between align-items-center">
     <h5 class="mb-0 text-primary">
       <i class="fa fa-users"></i> Enrolled Students
     </h5>
     @if($course->status !== 'completed' && $course->status !== 'cancelled')
         @if($course->status !== 'completed' && $course->status !== 'cancelled')
         <a href="#" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#enrollStudentModal">
         <i class="fa fa-user-plus"></i> Enroll Student
         </a>
         @endif

         <div class="modal fade" id="enrollStudentModal" tabindex="-1">
         <div class="modal-dialog">
         <form id="enrollStudentForm" method="POST" action="{{ route('admin.courses.enroll',$course) }}">
            @csrf
            <div class="modal-content">
               <div class="modal-header">
               <h5 class="modal-title"><i class="fa fa-user-plus"></i> Enroll New Student</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
               </div>
               <div class="modal-body">
                  @csrf
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

     @endif
   </div>
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
                                      <button
                                          class="btn btn-warning btn-sm"
                                          data-bs-toggle="modal"
                                          data-bs-target="#excludeStudentModal"
                                          data-student-id="{{ $student->id }}"
                                          data-course-id="{{ $course->id }}"
                                      >
                                          <i class="fa fa-user-slash"></i> Exclude
                                      </button>
  
                                      <button
                                          class="btn btn-danger btn-sm"
                                          data-bs-toggle="modal"
                                          data-bs-target="#withdrawStudentModal"
                                          data-student-id="{{ $student->id }}"
                                          data-course-id="{{ $course->id }}"
                                      >
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
 

  <!-- Audit Logs Section -->
  <div class="card mt-4 ">
    <div class="card-header bg-light text-primary">
      <h5 class="mb-0 text-primary">
        <i class="fa fa-history"></i> Audit Logs
      </h5>
    </div>
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
              @foreach($logs as $idx => $log)
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

  @if($course->status === 'ongoing')
    <div class="mt-4 text-end">
      <button
        class="btn btn-danger"
        data-bs-toggle="modal"
        data-bs-target="#cancelCourseModal"
        data-course-id="{{ $course->id }}"
      >
        <i class="fa fa-ban"></i> Cancel Course
      </button>
    </div>
  @endif
</div>

<!-- Remove Student Modal -->
<div class="modal fade" id="removeStudentModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="removeStudentForm" method="POST">
      @csrf
      @method('DELETE')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fa fa-user-times"></i> Remove Student
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
          <h5 class="modal-title">
            <i class="fa fa-ban"></i> Cancel Course
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to cancel this course? This action cannot be undone.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            No
          </button>
          <button type="submit" class="btn btn-danger">
            Yes, Cancel
          </button>
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
          <h5 class="modal-title">
            <i class="fa fa-check"></i> Complete Course
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to mark this course as completed?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            No
          </button>
          <button type="submit" class="btn btn-success">
            Yes, Complete
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Audit Changes Modal (View old/new values) -->
<div class="modal fade" id="auditChangeModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa fa-eye"></i> Audit Changes
        </h5>
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
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Close
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Remove Student
    document.querySelectorAll('[data-bs-target="#removeStudentModal"]').forEach(button => {
      button.addEventListener("click", function () {
        const studentId = this.dataset.studentId;
        const courseId  = this.dataset.courseId;
        document.getElementById("removeStudentForm").setAttribute("action", "/admin/courses/" + courseId + "/students/" + studentId);
      });
    });

    // Cancel Course
    document.querySelectorAll('[data-bs-target="#cancelCourseModal"]').forEach(button => {
      button.addEventListener("click", function () {
        const courseId = this.dataset.courseId;
        document.getElementById("cancelCourseForm").setAttribute("action", "/admin/courses/" + courseId + "/cancel");
      });
    });

    // Complete Course
    document.querySelectorAll('[data-bs-target="#completeCourseModal"]').forEach(button => {
      button.addEventListener("click", function () {
        const courseId = this.dataset.courseId;
        document.getElementById("completeCourseForm").setAttribute("action", "/admin/courses/" + courseId + "/complete");
      });
    });

    // Audit Logs: Show old/new values
    document.querySelectorAll('[data-bs-target="#auditChangeModal"]').forEach(button => {
      button.addEventListener("click", function () {
        const oldVals = this.dataset.logOld;
        const newVals = this.dataset.logNew;
        document.getElementById("logOldValues").textContent = JSON.stringify(JSON.parse(oldVals), null, 2);
        document.getElementById("logNewValues").textContent = JSON.stringify(JSON.parse(newVals), null, 2);
      });
    });
    
  });
</script>
<script>
   document.addEventListener("DOMContentLoaded", function () {
     // Exclude
     document.querySelectorAll('[data-bs-target="#excludeStudentModal"]').forEach(btn => {
       btn.addEventListener("click", function () {
         const courseId  = this.dataset.courseId;
         const studentId = this.dataset.studentId;
         document.getElementById("excludeStudentForm")
                 .setAttribute("action", `/admin/courses/${courseId}/students/${studentId}/exclude`);
       });
     });
     
     // Withdraw
     document.querySelectorAll('[data-bs-target="#withdrawStudentModal"]').forEach(btn => {
       btn.addEventListener("click", function () {
         const courseId  = this.dataset.courseId;
         const studentId = this.dataset.studentId;
         document.getElementById("withdrawStudentForm")
                 .setAttribute("action", `/admin/courses/${courseId}/students/${studentId}/withdraw`);
       });
     });
   });
 </script>
 
@endsection
