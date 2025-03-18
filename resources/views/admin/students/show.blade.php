@extends('layouts.app')

@section('title', 'Student Profile')

@section('breadcrumb')
<li class="breadcrumb-item">
   <a href="{{ route('admin.dashboard') }}">
     <i class="fa fa-tachometer-alt"></i> Dashboard
   </a>
</li>
<li class="breadcrumb-item">
   <a href="{{ route('admin.students.index') }}">
     <i class="fa fa-users"></i> Students
   </a>
</li>
<li class="breadcrumb-item active">
   <i class="fa fa-eye"></i> Profile
</li>
@endsection

@section('content')
<div class="container">
  <div class="card mt-4">
    <div class="card-header bg-light text-primary d-flex justify-content-between align-items-center">
      <h4><i class="fa fa-user"></i> {{ $student->name }}'s Profile</h4>
      <a href="{{ route('admin.students.index') }}" class="btn btn-light">
         <i class="fa fa-arrow-left"></i> Back
      </a>
    </div>
    <div class="card-body">
      <div class="row align-items-center">
        <div class="col-md-4 text-center">
          @if($student->avatar)
            <img src="{{ asset('storage/' . $student->avatar) }}" alt="Avatar" width="180" height="180">
          @else
            <img src="https://via.placeholder.com/180" alt="Avatar">
          @endif
          <h3 class="mt-3 text-primary">{{ $student->name }}</h3>
          <p class="text-muted">{{ ucfirst($student->specialization ?? 'Not specified') }}</p>
        </div>
        <div class="col-md-8">
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <tbody>
                <tr>
                  <th class="bg-light text-primary"><i class="fa fa-phone"></i> Phone</th>
                  <td>{{ $student->phone }}</td>
                </tr>
                <tr>
                  <th class="bg-light text-primary"><i class="fa fa-city"></i> City</th>
                  <td>{{ $student->city ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <th class="bg-light text-primary"><i class="fa fa-birthday-cake"></i> Age</th>
                  <td>{{ $student->age ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <th class="bg-light text-primary"><i class="fa fa-venus-mars"></i> Gender</th>
                  <td>{{ ucfirst($student->gender) }}</td>
                </tr>
                <tr>
                  <th class="bg-light text-primary"><i class="fa fa-phone-alt"></i> Emergency Phone</th>
                  <td>{{ $student->emergency_phone ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <th class="bg-light text-primary"><i class="fa fa-code"></i> Skills to Develop</th>
                  <td>
                    @if($student->skills->isNotEmpty())
                      @foreach($student->skills as $skill)
                        <span class="badge bg-info me-1">{{ $skill->name }}</span>
                      @endforeach
                    @else
                      <span>N/A</span>
                    @endif
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Courses Table -->
      <div class="row align-items-center mt-3">
        <div class="col-md-12">
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <th>#</th>
                <th>Course Type</th>
                <th>Group Type</th>
                <th>Status</th>
                <th>Instructor</th>
                <th>From Date</th>
                <th>To Date</th>
                <th>Actions</th>
              </thead>
              <tbody>
                @foreach ($student->courses as $course)
                  <tr>
                    <th>{{ $loop->iteration }}</th>
                    <td>{{ $course->courseType?->name }}</td>
                    <td>{{ $course->groupType?->name }}</td>
                    <td>
                      <span class="badge bg-{{ 
                        $course->pivot->status === 'upcoming'  ? 'warning' : (
                        $course->pivot->status === 'ongoing'   ? 'info'    : (
                        $course->pivot->status === 'completed' ? 'success' : (
                        $course->pivot->status === 'cancelled' ? 'danger'  : 'secondary')))
                      }}">
                        {{ ucfirst($course->pivot->status) }}
                      </span>
                    </td>
                    <td>{{ $course->instructor?->name }}</td>
                    <td>{{ $course->start_date }}</td>
                    <td>{{ $course->end_date }}</td>
                    <td>
                      @if($course->pivot->status === 'ongoing')
                        <!-- زر استبعاد الطالب -->
                        <button class="btn btn-warning btn-sm"
                          data-bs-toggle="modal"
                          data-bs-target="#excludeStudentModal"
                          data-student-id="{{ $student->id }}"
                          data-course-id="{{ $course->id }}">
                          <i class="fa fa-user-slash"></i> Exclude
                        </button>

                        <!-- زر سحب الطالب -->
                        <button class="btn btn-danger btn-sm"
                          data-bs-toggle="modal"
                          data-bs-target="#withdrawStudentModal"
                          data-student-id="{{ $student->id }}"
                          data-course-id="{{ $course->id }}">
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
        </div>
      </div>

      <!-- Student Files Table -->
      <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="mb-0"><i class="fa fa-folder"></i> Student Files</h4>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFileModal">
            <i class="fa fa-upload"></i> Add File
          </button>
        </div>
        <div class="card-body">
          @if($student->files->count())
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>File Name</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($student->files as $file)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $file->name }}</td>
                      <td>
                        <a href="{{ route('admin.students.files.download', [$student->id, $file->id]) }}" class="btn btn-success btn-sm">
                          <i class="fa fa-download"></i> Download
                        </a>
                        <button 
                          class="btn btn-warning btn-sm"
                          data-bs-toggle="modal"
                          data-bs-target="#editFileModal"
                          data-file-id="{{ $file->id }}"
                          data-file-name="{{ $file->name }}">
                          <i class="fa fa-edit"></i> Edit
                        </button>
                        <button
                          class="btn btn-danger btn-sm"
                          data-bs-toggle="modal"
                          data-bs-target="#deleteFileModal"
                          data-file-id="{{ $file->id }}">
                          <i class="fa fa-trash"></i> Delete
                        </button>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <p class="text-muted">No files uploaded yet.</p>
          @endif
        </div>
      </div>
    </div>
    <div class="card-footer text-end">
      <!-- Enroll Button to Open Course Suggestions Modal -->
      <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#courseSuggestionsModal">
        <i class="fa fa-book"></i> Enroll in Course
      </button>
      <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-warning">
        <i class="fa fa-edit"></i> Edit Profile
      </a>

      {{-- print suggestion coureses button --}}
      <a href="{{ route('admin.students.print_suggestion_courses', $student->id) }}" class="btn btn-primary">
        <i class="fa fa-print"></i> Print Suggestions Courses </a>
    </div>
  </div>
</div>


<!-- Add File Modal -->
<div class="modal fade" id="addFileModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="addFileForm"
      action="{{ route('admin.students.files.store', $student->id) }}"
      method="POST" enctype="multipart/form-data">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-upload"></i> Add New File</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>File Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Upload File</label>
            <input type="file" name="file" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" type="submit">
            <i class="fa fa-upload"></i> Upload
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Cancel
          </button>
        </div>
      </div>
    </form>
  </div>
</div>


<!-- Edit File Modal -->
<div class="modal fade" id="editFileModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="editFileForm" action="#" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-edit"></i> Edit File</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>File Name</label>
            <input type="text" name="name" id="editFileName" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Replace File (optional)</label>
            <input type="file" name="file" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" type="submit">
            <i class="fa fa-save"></i> Save
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Cancel
          </button>
        </div>
      </div>
    </form>
  </div>
</div>


<!-- Delete File Modal -->
<div class="modal fade" id="deleteFileModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="deleteFileForm" action="#" method="POST">
      @csrf
      @method('DELETE')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-trash"></i> Delete File</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this file?
        </div>
        <div class="modal-footer">
          <button class="btn btn-danger" type="submit">
            <i class="fa fa-trash"></i> Delete
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Cancel
          </button>
        </div>
      </div>
    </form>
  </div>
</div>


<!-- Exclude / Withdraw / Cancel Course and other modals remain unchanged... -->


<!-- Course Suggestions Modal -->
<div class="modal fade" id="courseSuggestionsModal" tabindex="-1" aria-labelledby="courseSuggestionsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-lg">
    <form action="{{ route('admin.courses.enroll', 1) }}" method="POST">
      @csrf
      <!-- Pass the student's id -->
      <input type="hidden" name="student_id" value="{{ $student->id }}">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="courseSuggestionsModalLabel"><i class="fa fa-book"></i> Course Suggestions</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Course Selector -->
          <div class="mb-3">
            <label for="course_id" class="form-label">Select Course</label>
            <select name="course_id" id="course_id" class="form-select">
              <option value="">-- Select Course --</option>
              @foreach($courses as $course)
                <option value="{{ $course->id }}">
                  {{ $course->courseType->name }} - {{ $course->status }} ({{ $course->start_date }} to {{ $course->end_date }})
                </option>
              @endforeach
            </select>
          </div>
          <!-- Course Preview Container -->
          <div id="course-preview" class="mt-3"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fa fa-user-plus"></i> Enroll Student</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Parse courses data passed from controller into JS
  var coursesData = @json($courses);

  document.addEventListener("DOMContentLoaded", function() {
    const courseSelect = document.getElementById('course_id');
    const previewContainer = document.getElementById('course-preview');

    if(courseSelect) {
      courseSelect.addEventListener('change', function() {
        const selectedId = this.value;
        if(selectedId === "") {
          previewContainer.innerHTML = "";
          return;
        }
        // Find the course in coursesData
        const course = coursesData.find(c => c.id == selectedId);
        if(course) {
          let html = '<h6>Course Details</h6>';
          html += '<div class="table-responsive"><table class="table table-sm table-bordered">';
          html += '<tr><th>ID</th><td>' + course.id + '</td></tr>';
          html += '<tr><th>Course Type</th><td>' + (course.course_type ? course.course_type.name : course.course_type_id) + '</td></tr>';
          html += '<tr><th>Instructor</th><td>' + (course.instructor ? course.instructor.name : course.instructor_id) + '</td></tr>';
          html += '<tr><th>Group Type</th><td>' + (course.group_type ? course.group_type.name : course.group_type_id) + '</td></tr>';
          html += '<tr><th>Status</th><td>' + course.status + '</td></tr>';
          html += '<tr><th>Start Date</th><td>' + course.start_date + '</td></tr>';
          html += '<tr><th>End Date</th><td>' + course.end_date + '</td></tr>';
          html += '<tr><th>Capacity</th><td>' + course.student_capacity + '</td></tr>';
          html += '<tr><th class="bg-success text-light">Current Students Count : </th><td>' + course.students_count + '</td></tr>';
          html += '</table></div>';
          previewContainer.innerHTML = html;
        }
      });
    }
  });
</script>

<script>
  // Example: hooking up the Edit and Delete modals for Student Files
  document.addEventListener("DOMContentLoaded", function () {
    // Edit File
    const editFileModal = document.getElementById('editFileModal');
    const editFileNameInput = document.getElementById('editFileName');
    const editFileForm = document.getElementById('editFileForm');

    editFileModal.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget; 
      const fileId = button.getAttribute('data-file-id');
      const fileName = button.getAttribute('data-file-name');
      // Set the form's action to something like:
      editFileForm.setAttribute('action', `/admin/students/files/${fileId}`); 
      // Populate the text input
      editFileNameInput.value = fileName;
    });

    // Delete File
    const deleteFileModal = document.getElementById('deleteFileModal');
    const deleteFileForm = document.getElementById('deleteFileForm');

    deleteFileModal.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const fileId = button.getAttribute('data-file-id');
      // Set the form action to /admin/students/files/{fileId}
      deleteFileForm.setAttribute('action', `/admin/students/files/${fileId}`);
    });
  });
</script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Exclude Student
    document.querySelectorAll('[data-bs-target="#excludeStudentModal"]').forEach(btn => {
      btn.addEventListener("click", function () {
        const courseId  = this.dataset.courseId;
        const studentId = this.dataset.studentId;
        document.getElementById("excludeStudentForm")
                .setAttribute("action", `/admin/courses/${courseId}/students/${studentId}/exclude`);
      });
    });
    
    // Withdraw Student
    document.querySelectorAll('[data-bs-target="#withdrawStudentModal"]').forEach(btn => {
      btn.addEventListener("click", function () {
        const courseId  = this.dataset.courseId;
        const studentId = this.dataset.studentId;
        document.getElementById("withdrawStudentForm")
                .setAttribute("action", `/admin/courses/${courseId}/students/${studentId}/withdraw`);
      });
    });

    // Cancel Course
    document.querySelectorAll('[data-bs-target="#cancelCourseModal"]').forEach(button => {
      button.addEventListener("click", function () {
        let courseId = this.dataset.courseId;
        document.getElementById("cancelCourseForm").setAttribute("action", "/admin/courses/" + courseId + "/cancel");
      });
    });
    // etc...
  });
</script>
@endpush
