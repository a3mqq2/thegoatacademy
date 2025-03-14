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
  <div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">
        <i class="fa fa-list"></i> Course List
      </h4>
   
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
                <th>Capacity</th>
                <th>Status</th>
                <th>Actions</th>
             </tr>
           </thead>
           <tbody>
              @forelse($courses as $course)
                <tr>
                  <td>{{ $course->id }}</td>
                  <td>
                    <!-- If you store course name in the CourseType or have a direct column, adjust as needed -->
                    {{ $course->courseType->name ?? 'N/A' }}
                  </td>
                  <td>{{ $course->instructor->name ?? 'N/A' }}</td>
                  <td>{{ $course->start_date }}</td>
                  <td>{{ $course->mid_exam_date }}</td>
                  <td>{{ $course->final_exam_date }}</td>
                  <td>{{ $course->student_capacity }}</td>
                  <!-- Status Badge -->
                  <td>
                    <span class="badge bg-{{ 
                      $course->status === 'upcoming'  ? 'warning' : (
                      $course->status === 'ongoing'   ? 'info'    : (
                      $course->status === 'completed' ? 'success' : (
                      $course->status === 'cancelled' ? 'danger'  : 'secondary')))
                    }}">
                      {{ ucfirst($course->status) }}
                    </span>
                  </td>
                  <td>
                    <!-- Example: Manage or view details -->

                    <a href="{{ route('admin.courses.show', $course->id) }}"
                     class="btn btn-primary btn-sm"
                  >
                    <i class="fa fa-eye"></i> show
                  </a>

                 

                    <!-- Example: If there's a way to cancel or complete a course -->
                    @if($course->status === 'ongoing')

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
                   <td colspan="9" class="text-center">No courses found</td>
                </tr>
              @endforelse
           </tbody>
         </table>
      </div>
      <!-- Pagination -->
      {{ $courses->appends(request()->query())->links() }}
    </div>
  </div>
</div>

<!-- Cancel Confirmation Modal (If needed) -->
<div class="modal fade" id="cancelModal" tabindex="-1">
   <div class="modal-dialog">
     <form id="cancelForm" method="POST">
       @csrf
       @method('PUT')
       <div class="modal-content">
         <div class="modal-header">
           <h5 class="modal-title"><i class="fa fa-ban"></i> Confirm Cancellation</h5>
           <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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

<!-- Delete Confirmation Modal -->
{{--  --}}

<script>
   document.addEventListener("DOMContentLoaded", function () {
     // This code will set the form's action to "/admin/courses/{id}/cancel"
     // whenever you click a "cancel" button that opens the modal.
     document.querySelectorAll('[data-bs-target="#cancelModal"]').forEach(btn => {
       btn.addEventListener("click", function () {
         let courseId = this.dataset.courseId;
         document.getElementById("cancelForm").setAttribute("action", "/admin/courses/" + courseId + "/cancel");
       });
     });
   });
 </script>
@endsection
