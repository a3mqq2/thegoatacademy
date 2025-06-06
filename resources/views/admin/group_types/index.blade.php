@extends('layouts.app')

@section('title', 'Group Types')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
  <li class="breadcrumb-item active">
    <i class="fa fa-users"></i> Group Types
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
         <!-- Advanced Filter Form -->
         <form action="{{ route('admin.group-types.index') }}" method="GET" class="mb-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="name" class="form-label">
                        <i class="fa fa-tag"></i> Name
                    </label>
                    <input type="text" name="name" id="name" value="{{ request('name') }}" class="form-control" placeholder="Enter name">
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label">
                        <i class="fa fa-toggle-on"></i> Status
                    </label>
                    <select name="status" id="status" class="form-select">
                        <option value="">-- Select Status --</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="student_capacity" class="form-label">
                        <i class="fa fa-user-graduate"></i> Minimum Student Capacity
                    </label>
                    <input type="number" name="student_capacity" id="student_capacity" value="{{ request('student_capacity') }}" class="form-control" placeholder="Enter minimum capacity">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-secondary me-2">
                        <i class="fa fa-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.group-types.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-sync-alt"></i> Reset
                    </a>
                </div>
            </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Group Types List -->
  <div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">
        <i class="fa fa-list"></i> Group Type List
      </h4>
      <a href="{{ route('admin.group-types.create') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i> Create New Group Type
      </a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
         <table class="table table-bordered table-hover mb-0">
           <thead>
             <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Student Capacity</th>
                <th>Lesson Duration</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
             </tr>
           </thead>
           <tbody>
              @forelse($groupTypes as $groupType)
                <tr>
                  <td>{{ $groupType->id }}</td>
                  <td><i class="fa fa-tag"></i> {{ $groupType->name }}</td>
                  <td><i class="fa fa-user-graduate"></i> {{ $groupType->student_capacity }}</td>
                  <td>
                    <i class="fa fa-clock"></i> {{ $groupType->lesson_duration }}
                  </td>
                  <td>
                    @if($groupType->status == "active")
                      <span class="badge bg-success"><i class="fa fa-check"></i> Active</span>
                    @else
                      <span class="badge bg-danger"><i class="fa fa-times"></i> Inactive</span>
                    @endif
                  </td>
                  <td><i class="fa fa-calendar"></i> {{ $groupType->created_at->format('Y-m-d') }}</td>
                  <td>
                    <a href="{{ route('admin.group-types.edit', $groupType->id) }}" class="btn btn-warning btn-sm">
                      <i class="fa fa-edit"></i> Edit
                    </a>
                    <!-- Toggle Status Form -->
                    <form action="{{ route('admin.group-types.toggle', $groupType->id) }}" method="POST" class="d-inline-block">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-sm {{ $groupType->status == 'active' ? 'btn-warning' : 'btn-success' }}">
                          <i class="fa {{ $groupType->status == 'active' ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                          {{ $groupType->status == 'active' ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <form action="{{ route('admin.group-types.destroy', $groupType->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                          <i class="fa fa-trash"></i> Delete
                        </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                   <td colspan="6" class="text-center">No group types found</td>
                </tr>
              @endforelse
           </tbody>
         </table>
      </div>
      <!-- Pagination links -->
      @if(method_exists($groupTypes, 'links'))
        <div class="mt-3">
          {{ $groupTypes->appends(request()->query())->links() }}
        </div>
      @endif
    </div>
  </div>
</div>
@endsection

@push('styles')
  {{-- Add any additional page-specific CSS here --}}
@endpush

@push('scripts')
  {{-- Add any additional page-specific JavaScript here --}}
@endpush
