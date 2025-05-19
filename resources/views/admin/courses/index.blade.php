@extends('layouts.app')

@section('title', 'Courses')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
  <li class="breadcrumb-item active">
    <i class="fa fa-book"></i> Courses
  </li>
@endsection

@section('content')
<div class="container">
  <!-- Filter Card -->
  <div class="row mt-3">
    <div class="card w-100">
      <div class="card-header">
         Filter <i class="fa fa-filter"></i>
      </div>
      <div class="card-body">
         <form action="{{ route('admin.courses.index') }}" method="GET" class="mb-3">
            <div class="row g-3">
                <!-- Example Filter Field: Course Name -->
                <div class="col-md-4">
                    <label for="course_name" class="form-label"><i class="fa fa-tag"></i> Course Name</label>
                    <input
                       type="text"
                       name="course_name"
                       id="course_name"
                       value="{{ request('course_name') }}"
                       class="form-control"
                       placeholder="Enter course name"
                    >
                </div>
                <!-- Example Filter Field: Instructor -->
                <div class="col-md-4">
                    <label for="instructor_id" class="form-label"><i class="fa fa-user"></i> Instructor</label>
                    <select
                       name="instructor_id"
                       id="instructor_id"
                       class="form-select"
                    >
                        <option value="">-- Select Instructor --</option>
                        @foreach($instructors as $instructor)
                          <option
                            value="{{ $instructor->id }}"
                            {{ request('instructor_id') == $instructor->id ? 'selected' : '' }}
                          >
                            {{ $instructor->name }}
                          </option>
                        @endforeach
                    </select>
                </div>
                <!-- Example Filter Field: Status -->
                <div class="col-md-4">
                    <label for="status" class="form-label"><i class="fa fa-toggle-on"></i> Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">-- Select Status --</option>
                        <option value="upcoming"  {{ request('status') == 'upcoming'  ? 'selected' : '' }}>Upcoming</option>
                        <option value="ongoing"   {{ request('status') == 'ongoing'   ? 'selected' : '' }}>Ongoing</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <!-- If you also want to filter "paused", you can add: -->
                        <option value="paused"    {{ request('status') == 'paused'    ? 'selected' : '' }}>Paused</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-secondary me-2">
                      <i class="fa fa-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">
                      <i class="fa fa-sync-alt"></i> Reset
                    </a>
                </div>
            </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Courses List -->
  <div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">
        <i class="fa fa-list"></i> Course List
      </h4>
      <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i> Add New Course
      </a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
         <table class="table table-bordered table-hover mb-0">
           <thead>
             <tr>
                <th>ID</th>
                <th>Course Name</th>
                <th>Instructor</th>
                <th>Start Date</th>
                <th>Mid Exam</th>
                <th>Final Exam</th>
                <th>Days</th>
                <th>Time</th>
                <th class="bg-primary text-light">Capacity</th>
                <th class="bg-success text-light">Students</th>
                <th class="bg-danger text-light">Due </th>
                <th>Meeting Platform</th>
                <th>Status</th>
                <th>Actions</th>
             </tr>
           </thead>
           <tbody>
              @forelse($courses as $course)
                <tr>
                  <td>{{ $course->id }}</td>
                  <td>
                    {{ $course->courseType->name ?? 'N/A' }}
                  </td>
                  <td>{{ $course->instructor->name ?? 'N/A' }}</td>
                  <td>{{ $course->start_date }}</td>
                  <td>{{ $course->mid_exam_date }}</td>
                  <td>{{ $course->final_exam_date }}</td>
                  <td>{{ $course->days }}</td>
                 
                  @php
                  $timeParts = explode(' - ', $course->time);
                  $start = $timeParts[0] ?? null;
                  $end   = $timeParts[1] ?? null;
                  $formattedStart = $start
                      ? \Carbon\Carbon::createFromFormat('H:i', $start)->format('h:i A')
                      : '';
                  $formattedEnd = $end
                      ? \Carbon\Carbon::createFromFormat('H:i', $end)->format('h:i A')
                      : '';
                @endphp
                <td>
                  {{ $formattedStart }}
                  @if($formattedStart && $formattedEnd)
                    - {{ $formattedEnd }}
                  @endif
                </td>

                
                
                  <td>{{ $course->student_capacity }}</td>
                  <td>{{ $course->student_count }}</td>
                  <td>{{ $course->student_capacity - $course->student_count }}</td>
                  <td>{{ $course->meetingPlatform->name ?? 'N/A' }}</td>
                  <td>
                    @if($course->status === 'paused')
                      <span class="badge bg-warning text-dark">Paused</span>
                    @else
                      <span class="badge bg-{{ 
                        $course->status === 'upcoming'  ? 'warning' : (
                        $course->status === 'ongoing'   ? 'info'    : (
                        $course->status === 'completed' ? 'success' : (
                        $course->status === 'cancelled' ? 'danger'  : 'secondary')))
                      }}">
                        {{ ucfirst($course->status) }}
                      </span>
                    @endif
                  </td>
                  <td>
                    <a href="{{ route('admin.courses.show', $course->id) }}"
                      class="btn btn-primary btn-sm"
                     >
                       <i class="fa fa-eye"></i> Show
                     </a>

                     
                     <a href="{{ route('admin.courses.print', $course->id) }}"
                      class="btn btn-secondary btn-sm"
                     >
                       <i class="fa fa-print"></i> Print
                     </a>

                     

                    @if($course->status === 'ongoing' || $course->status == "upcoming")
                      <a href="{{ route('admin.courses.edit', $course->id) }}"
                       class="btn btn-warning btn-sm"
                      >
                        <i class="fa fa-edit"></i> Edit
                      </a>
                      <button
                        class="btn btn-danger btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#cancelModal"
                        data-course-id="{{ $course->id }}"
                      >
                        <i class="fa fa-ban"></i> Cancel
                      </button>
                    @endif

                    <!-- If the course is paused, show a Reactivate button -->
                    @if($course->status === 'paused')
                      <button
                        class="btn btn-success btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#reactiveModal"
                        data-course-id="{{ $course->id }}"
                      >
                        <i class="fa fa-play"></i> Reactivate
                      </button>
                    @endif

                    <!-- Delete button -->
                    <button
                      class="btn btn-danger btn-sm"
                      data-bs-toggle="modal"
                      data-bs-target="#deleteModal"
                      data-course-id="{{ $course->id }}"
                    >
                      <i class="fa fa-trash"></i> Delete
                    </button>
                  </td>
                </tr>
              @empty
                <tr>
                   <td colspan="11" class="text-center">No courses found</td>
                </tr>
              @endforelse
           </tbody>
         </table>
      </div>
      {{ $courses->appends(request()->query())->links() }}
    </div>
  </div>
</div>

<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog">
     <form id="cancelForm" method="POST">
       @csrf
       @method('PUT')
       <div class="modal-content">
         <div class="modal-header">
           <h5 class="modal-title"><i class="fa fa-ban"></i> Confirm Cancellation</h5>
           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
           Are you sure you want to cancel this course?
         </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
           <button type="submit" class="btn btn-danger">Yes, Cancel</button>
         </div>
       </div>
     </form>
   </div>
</div>

<!-- Reactive (Unpause) Modal -->
<div class="modal fade" id="reactiveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="reactiveForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-play"></i> Reactivate Course</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to reactivate this course (currently paused)?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
          <button type="submit" class="btn btn-success">Yes, Reactivate</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="deleteForm" method="POST">
      @csrf
      @method('DELETE')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-trash"></i> Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this course?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Yes, Delete</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
   document.addEventListener("DOMContentLoaded", function () {
     // Set action for Cancel Modal
     document.querySelectorAll('[data-bs-target="#cancelModal"]').forEach(btn => {
       btn.addEventListener("click", function () {
         let courseId = this.dataset.courseId;
         document.getElementById("cancelForm").setAttribute("action", "/admin/courses/" + courseId + "/cancel");
       });
     });
     
     // Set action for Delete Modal
     document.querySelectorAll('[data-bs-target="#deleteModal"]').forEach(btn => {
       btn.addEventListener("click", function () {
         let courseId = this.dataset.courseId;
         document.getElementById("deleteForm").setAttribute("action", "/admin/courses/" + courseId);
       });
     });

     // Set action for Reactivate (Unpause) Modal
     document.querySelectorAll('[data-bs-target="#reactiveModal"]').forEach(btn => {
       btn.addEventListener("click", function() {
         let courseId = this.dataset.courseId;
         // For reactivating, you might do something like /admin/courses/{id}/resume
         document.getElementById("reactiveForm").setAttribute("action", "/admin/courses/" + courseId + "/reactive");
       });
     });
   });
</script>
@endsection
