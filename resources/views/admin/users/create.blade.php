@extends('layouts.app')

@section('title', 'Create User')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
  <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
  <li class="breadcrumb-item active">Create User</li>
@endsection

@section('content')
<div class="">
  <div class="row justify-content-center mt-3">
    <div class="col-md-12">
      <div class="card shadow">
        <div class="card-header bg-light text-primary">
          <h5 class="mb-0 text-primary"><i class="fa fa-user-plus"></i> Create New User</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="name" class="form-label"><i class="fa fa-user"></i> Name:</label>
                  <input type="text" name="name" id="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name') }}" placeholder="Enter full name" required>
                  @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="mb-3">
                  <label for="email" class="form-label"><i class="fa fa-envelope"></i> Email:</label>
                  <input type="email" name="email" id="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" placeholder="Enter email" required>
                  @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>
            </div> <!-- end row -->

            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="password" class="form-label"><i class="fa fa-lock"></i> Password:</label>
                  <input type="password" name="password" id="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="Enter password" required>
                  @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="col-md-6">
                <div class="mb-3">
                  <label for="password_confirmation" class="form-label"><i class="fa fa-lock"></i> Confirm Password:</label>
                  <input type="password" name="password_confirmation" id="password_confirmation"
                    class="form-control" placeholder="Confirm password" required>
                </div>
              </div>
            </div> <!-- end row -->

            <!-- Roles Section -->
            <div class="row">
              <div class="col-md-12">
                <label class="form-label"><i class="fa fa-user-shield"></i> Assign Roles:</label>
                <div class="mb-3 border p-3 rounded">
                  @foreach($roles as $role)
                    <div class="form-check form-switch">
                      <input class="form-check-input role-checkbox" type="checkbox"
                        name="roles[]" id="role_{{ $role->name }}" value="{{ $role->name }}"
                        data-role-id="{{ $role->name }}"
                        {{ (is_array(old('roles')) && in_array($role->id, old('roles'))) ? 'checked' : '' }}>
                      <label class="form-check-label" for="role_{{ $role->name }}">{{ ucfirst($role->name) }}</label>
                    </div>
                  @endforeach
                  @error('roles') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
              </div>
            </div> <!-- end row -->

            <!-- Permissions Section -->
            <div class="row">
              <div class="col-md-12">
                <label class="form-label"><i class="fa fa-key"></i> Permissions:</label>
                <div class="mb-3 border p-3 rounded bg-light">
                  @foreach($roles as $role)
                    <div class="permissions-group" data-role-id="{{ $role->name }}" style="display: none;">
                      <strong class="text-primary">{{ ucfirst($role->name) }} Permissions:</strong>
                      @foreach($role->permissions as $permission)
                        <div class="form-check">
                          <input class="form-check-input permission-checkbox"
                            type="checkbox" name="permissions[]" id="permission_{{ $permission->name }}"
                            value="{{ $permission->name }}">
                          <label class="form-check-label" for="permission_{{ $permission->name }}">
                            {{ ucfirst($permission->name) }}
                          </label>
                        </div>
                      @endforeach
                      <hr>
                    </div>
                  @endforeach
                </div>
              </div>
            </div> <!-- end row -->

            <div class="text-end">
              <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Cancel
              </a>
              <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> Create User
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", function () {
    function updatePermissions() {
      let selectedRoles = [];
      document.querySelectorAll('.role-checkbox:checked').forEach((el) => {
        selectedRoles.push(el.dataset.roleId);
      });

      document.querySelectorAll('.permissions-group').forEach((permGroup) => {
        let roleId = permGroup.dataset.roleId;
        permGroup.style.display = selectedRoles.includes(roleId) ? "block" : "none";
      });
    }

    // When a role checkbox is toggled
    document.querySelectorAll('.role-checkbox').forEach((checkbox) => {
      checkbox.addEventListener('change', updatePermissions);
    });

    // Run on load in case roles are pre-checked
    updatePermissions();
  });
</script>
@endpush
