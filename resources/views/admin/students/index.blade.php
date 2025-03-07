@extends('layouts.app')

@section('title', 'Students')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
  <li class="breadcrumb-item active">
    <i class="fa fa-users"></i> Students
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
         <form action="{{ route('admin.students.index') }}" method="GET" class="mb-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="name" class="form-label"><i class="fa fa-tag"></i> Name</label>
                    <input type="text" name="name" id="name" value="{{ request('name') }}" class="form-control" placeholder="Enter student name">
                </div>
                <div class="col-md-4">
                    <label for="phone" class="form-label"><i class="fa fa-phone"></i> Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ request('phone') }}" class="form-control" placeholder="Enter phone number">
                </div>
                {{-- <div class="col-md-4">
                    <label for="status" class="form-label"><i class="fa fa-toggle-on"></i> Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">-- Select Status --</option>
                        <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                        <option value="excluded" {{ request('status') == 'excluded' ? 'selected' : '' }}>Excluded</option>
                        <option value="withdrawn" {{ request('status') == 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                    </select>
                </div> --}}
            </div>
            <div class="row mt-3">
                <div class="col-md-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-secondary me-2"><i class="fa fa-search"></i> Filter</button>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary"><i class="fa fa-sync-alt"></i> Reset</a>
                </div>
            </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Students List -->
  <div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0"><i class="fa fa-list"></i> Student List</h4>
      <a href="{{ route('admin.students.create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add New Student</a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
         <table class="table table-bordered table-hover mb-0">
           <thead>
             <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                {{-- <th>Status</th> --}}
                {{-- <th>Withdrawal Reason </th> --}}
                <th>Books Due</th>
                <th>Actions</th>
             </tr>
           </thead>
           <tbody>
              @forelse($students as $student)
                <tr>
                  <td>{{ $student->id }}</td>
                  <td>{{ $student->name }}</td>
                  <td>{{ $student->phone }}</td>
                  {{-- <td>
                    <span class="badge bg-{{ $student->status == 'ongoing' ? 'success' : ($student->status == 'excluded' ? 'warning' : 'danger') }}">
                      {{ ucfirst($student->status) }}
                    </span>
                  </td> --}}
                  {{-- <td>
                    {{$student->withdrawal_reason}}
                  </td> --}}
                  <td>
                    <span class="badge bg-{{ $student->books_due ? 'danger' : 'success' }}">
                      {{ $student->books_due ? 'Due' : 'Cleared' }}
                    </span>
                  </td>
                  <td>
                    <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-warning btn-sm">
                      <i class="fa fa-edit"></i> Edit
                    </a>
                    {{-- @if ($student->status == 'ongoing')
                      <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#excludeModal" data-student-id="{{ $student->id }}">
                        <i class="fa fa-user-slash"></i> Exclude
                      </button>
                      <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#withdrawModal" data-student-id="{{ $student->id }}">
                        <i class="fa fa-sign-out-alt"></i> Withdraw
                      </button>
                    @endif --}}
                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-student-id="{{ $student->id }}">
                      <i class="fa fa-trash"></i> Delete
                    </button>
                  </td>
                </tr>
              @empty
                <tr>
                   <td colspan="6" class="text-center">No students found</td>
                </tr>
              @endforelse
           </tbody>
         </table>
      </div>
      {{ $students->appends(request()->query())->links() }}
    </div>
  </div>
</div>

<!-- Modals -->
<div class="modal fade" id="excludeModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="excludeForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Exclusion</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to exclude this student?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">Exclude</button>
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
           <h5 class="modal-title"><i class="fa fa-trash"></i> Confirm Deletion</h5>
           <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body">
           Are you sure you want to delete this student? This action cannot be undone.
         </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
           <button type="submit" class="btn btn-danger">Delete</button>
         </div>
       </div>
     </form>
   </div>
 </div>
 
 <script>
   document.addEventListener("DOMContentLoaded", function () {
     document.querySelectorAll('[data-bs-target="#deleteModal"]').forEach(button => {
       button.addEventListener("click", function () {
         document.getElementById("deleteForm").setAttribute("action", "/admin/students/" + this.dataset.studentId);
       });
     });
   });
 </script>


{{-- <div class="modal fade" id="withdrawModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="withdrawForm" method="POST">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Withdrawal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label for="withdrawal_reason">Reason:</label>
          <input type="text" name="withdrawal_reason" class="form-control" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Withdraw</button>
        </div>
      </div>
    </form>
  </div>
</div> --}}

<script>
  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('[data-bs-target="#excludeModal"]').forEach(button => {
      button.addEventListener("click", function () {
        document.getElementById("excludeForm").setAttribute("action", "/admin/students/" + this.dataset.studentId + "/exclude");
      });
    });

    document.querySelectorAll('[data-bs-target="#withdrawModal"]').forEach(button => {
      button.addEventListener("click", function () {
        document.getElementById("withdrawForm").setAttribute("action", "/admin/students/" + this.dataset.studentId + "/withdraw");
      });
    });
  });
</script>
@endsection
