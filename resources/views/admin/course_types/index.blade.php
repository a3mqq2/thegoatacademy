@extends('layouts.app')

@section('title', 'Course Types')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
  <li class="breadcrumb-item active">
    <i class="fa fa-book"></i> Course Types
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
         <form action="{{ route('admin.course-types.index') }}" method="GET" class="mb-3">
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
                    <label for="duration" class="form-label">
                        <i class="fa fa-clock"></i> Duration
                    </label>
                    <select name="duration" id="duration" class="form-select">
                        <option value="">-- Select Duration --</option>
                        <option value="week" {{ request('duration') == 'week' ? 'selected' : '' }}>Week</option>
                        <option value="month" {{ request('duration') == 'month' ? 'selected' : '' }}>Month</option>
                        <option value="half_year" {{ request('duration') == 'half_year' ? 'selected' : '' }}>Half Year</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-secondary me-2">
                        <i class="fa fa-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.course-types.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-sync-alt"></i> Reset
                    </a>
                </div>
            </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Course Types List -->
  <div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">
        <i class="fa fa-list"></i> Course Type List
      </h4>
      <a href="{{ route('admin.course-types.create') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i> Create New Course Type
      </a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
         <table class="table table-bordered table-hover mb-0">
           <thead>
             <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Status</th>
                <th>Duration</th>
                <th>Created At</th>
                <th>Actions</th>
             </tr>
           </thead>
           <tbody>
              @forelse($courseTypes as $courseType)
                <tr>
                  <td>{{ $courseType->id }}</td>
                  <td><i class="fa fa-tag"></i> {{ $courseType->name }}</td>
                  <td>
                    @if($courseType->status == "active")
                      <span class="badge bg-success"><i class="fa fa-check"></i> Active</span>
                    @else
                      <span class="badge bg-danger"><i class="fa fa-times"></i> Inactive</span>
                    @endif
                  </td>
                  <td>
                    @if($courseType->duration)
                      <span class="badge bg-info"><i class="fa fa-clock"></i> {{ ucfirst(str_replace('_', ' ', $courseType->duration)) }}</span>
                    @else
                      <span class="text-muted"><i class="fa fa-exclamation-circle"></i> Not Set</span>
                    @endif
                  </td>
                  <td><i class="fa fa-calendar"></i> {{ $courseType->created_at->format('Y-m-d') }}</td>
                  <td>
                    <a href="{{ route('admin.course-types.edit', $courseType->id) }}" class="btn btn-warning btn-sm">
                      <i class="fa fa-edit"></i> Edit
                    </a>
                    <!-- Toggle Status Form -->
                    <form action="{{ route('admin.course-types.toggle', $courseType->id) }}" method="POST" class="d-inline-block">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-sm {{ $courseType->status == 'active' ? 'btn-warning' : 'btn-success' }}">
                          <i class="fa {{ $courseType->status == 'active' ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                          {{ $courseType->status == 'active' ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <form action="{{ route('admin.course-types.destroy', $courseType->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure?');">
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
                   <td colspan="6" class="text-center">No course types found</td>
                </tr>
              @endforelse
           </tbody>
         </table>
      </div>
      <!-- Pagination links -->
      @if(method_exists($courseTypes, 'links'))
        <div class="mt-3">
          {{ $courseTypes->appends(request()->query())->links() }}
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
