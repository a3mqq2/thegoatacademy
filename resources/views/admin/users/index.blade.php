@extends('layouts.app')

@section('title', 'Users')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
  <li class="breadcrumb-item active">
    <i class="fa fa-users"></i> Users
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
         <form action="{{ route('admin.users.index') }}" method="GET" class="mb-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="name" class="form-label">
                        <i class="fa fa-user"></i> Name
                    </label>
                    <input type="text" name="name" id="name" value="{{ request('name') }}" class="form-control" placeholder="Enter name">
                </div>
                <div class="col-md-3">
                    <label for="email" class="form-label">
                        <i class="fa fa-envelope"></i> Email
                    </label>
                    <input type="text" name="email" id="email" value="{{ request('email') }}" class="form-control" placeholder="Enter email">
                </div>
                <div class="col-md-3">
                    <label for="role" class="form-label">
                        <i class="fa fa-user-tag"></i> Role
                    </label>
                    <select name="role" id="role" class="form-select">
                        <option value="">-- Select Role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">
                        <i class="fa fa-toggle-on"></i> Status
                    </label>
                    <select name="status" id="status" class="form-select">
                        <option value="">-- Select Status --</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-secondary me-2">
                        <i class="fa fa-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-sync-alt"></i> Reset
                    </a>
                </div>
            </div>
        </form>
        
      </div>
    </div>
  </div>

  <!-- Users List Card -->
  <div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">
        <i class="fa fa-list"></i> User List
      </h4>
      <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i> Create New User
      </a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
         <table class="table table-bordered table-hover mb-0">
           <thead>
             <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Roles</th>
                <th>Status</th>
                <th>Skills</th> <!-- New column -->
                <th>Levels</th> <!-- New column -->
                <th>Created At</th>
                <th>Actions</th>
             </tr>
           </thead>
           <tbody>
              @forelse($users as $user)
                <tr>
                  <td>{{ $user->id }}</td>
                  <td>
                    <i class="fa fa-user"></i> {{ $user->name }}
                  </td>
                  <td>
                    <i class="fa fa-envelope"></i> {{ $user->email }}
                  </td>
                  <td>
                    @if($user->roles->isNotEmpty())
                      @foreach($user->roles as $role)
                        <span class="badge bg-secondary">
                          <i class="fa fa-tag"></i> {{ $role->name }}
                        </span>
                      @endforeach
                    @else
                      <span class="text-muted">
                        <i class="fa fa-exclamation-circle"></i> No Roles
                      </span>
                    @endif
                  </td>
                  <td>
                    @if($user->status == "active")
                      <span class="badge bg-success">
                        <i class="fa fa-check"></i> Active
                      </span>
                    @else
                      <span class="badge bg-danger">
                        <i class="fa fa-times"></i> Inactive
                      </span>
                    @endif
                  </td>
                  <td>
                    @if($user->skills && $user->skills->isNotEmpty())
                      @foreach($user->skills as $skill)
                        <span class="badge bg-info">
                          <i class="fa fa-code"></i> {{ $skill->name }}
                        </span>
                      @endforeach
                    @else
                      <span class="text-muted">
                        <i class="fa fa-exclamation-circle"></i> No Skills
                      </span>
                    @endif
                  </td>
                  <td>
                    @if($user->levels && $user->levels->isNotEmpty())
                      @foreach($user->levels as $skill)
                        <span class="badge bg-info">
                          <i class="fa fa-code"></i> {{ $skill->name }}
                        </span>
                      @endforeach
                    @else
                      <span class="text-muted">
                        <i class="fa fa-exclamation-circle"></i> No levels
                      </span>
                    @endif
                  </td>
                  <td>
                    <i class="fa fa-calendar"></i> {{ $user->created_at->format('Y-m-d') }}
                  </td>
                  <td>
                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-info btn-sm">
                      <i class="fa fa-eye"></i> View
                    </a>
                     @if ($user->id != 1)
                      <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning btn-sm">
                        <i class="fa fa-edit"></i> Edit
                      </a>
                      <!-- Toggle Status Form -->
                      <form action="{{ route('admin.users.toggle', $user->id) }}" method="POST" class="d-inline-block">
                          @csrf
                          @method('PUT')
                          <button type="submit" class="btn btn-sm {{ $user->active ? 'btn-warning' : 'btn-success' }}">
                            <i class="fa {{ $user->active ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                            {{ $user->active ? 'Deactivate' : 'Activate' }}
                          </button>
                      </form>
                      <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure?');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fa fa-trash"></i> Delete
                          </button>
                      </form>
                     @endif
                  </td>
                </tr>
              @empty
                <tr>
                   <!-- Updated colspan to 8 to match new Skills column -->
                   <td colspan="8" class="text-center">No users found</td>
                </tr>
              @endforelse
           </tbody>
         </table>
      </div>
      <!-- Pagination links -->
      @if(method_exists($users, 'links'))
        <div class="mt-3">
          {{ $users->appends(request()->query())->links() }}
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
