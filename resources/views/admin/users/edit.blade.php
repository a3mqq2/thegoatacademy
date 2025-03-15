@extends('layouts.app')

@section('title', 'Edit User')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
  <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
  <li class="breadcrumb-item active">Edit User</li>
@endsection

@section('content')
<div class="">
  <div class="row justify-content-center mt-3">
    <div class="col-md-12">
      <div class="card shadow">
        <div class="card-header bg-light text-primary">
          <h5 class="mb-0 text-primary"><i class="fa fa-user-edit"></i> Edit User</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
              <!-- Basic Information -->
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="name" class="form-label"><i class="fa fa-user"></i> Name:</label>
                  <input type="text" name="name" id="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $user->name) }}" placeholder="Enter full name" required>
                  @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="mb-3">
                  <label for="phone" class="form-label"><i class="fa fa-phone"></i> phone:</label>
                  <input type="text" name="phone" id="phone"
                    class="form-control @error('phone') is-invalid @enderror"
                    value="{{ old('phone', $user->phone) }}" placeholder="Enter full phone" required>
                  @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="mb-3">
                  <label for="email" class="form-label"><i class="fa fa-envelope"></i> Email:</label>
                  <input type="email" name="email" id="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $user->email) }}" placeholder="Enter email" required>
                  @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>
            </div> <!-- end row -->
            
            <div class="row">
              <!-- Password (optional: leave blank if unchanged) -->
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="password" class="form-label"><i class="fa fa-lock"></i> Password:</label>
                  <input type="password" name="password" id="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="Enter new password (leave blank if not changing)">
                  @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>
              
              <!-- Confirm Password -->
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="password_confirmation" class="form-label"><i class="fa fa-lock"></i> Confirm Password:</label>
                  <input type="password" name="password_confirmation" id="password_confirmation"
                    class="form-control" placeholder="Confirm new password">
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
                        {{ (is_array(old('roles', $user->roles->pluck('name')->toArray())) && in_array($role->name, old('roles', $user->roles->pluck('name')->toArray()))) ? 'checked' : '' }}>
                      <label class="form-check-label" for="role_{{ $role->name }}">{{ ucfirst($role->name) }}</label>
                    </div>
                  @endforeach
                  @error('roles') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
              </div>
            </div> <!-- end row -->
            
            <!-- Additional Fields (Previously "Instructor" fields, now always visible) -->
            <div id="instructorFields">
              <div class="row">
                <!-- Age -->
                <div class="col-md-4">
                  <div class="mb-3">
                    <label for="age" class="form-label"><i class="fa fa-calendar"></i> Age:</label>
                    <input type="number" name="age" id="age"
                      class="form-control @error('age') is-invalid @enderror"
                      value="{{ old('age', $user->age) }}" placeholder="Enter age">
                    @error('age') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                </div>
                <!-- Gender -->
                <div class="col-md-4">
                  <div class="mb-3">
                    <label for="gender" class="form-label"><i class="fa fa-venus-mars"></i> Gender:</label>
                    <select name="gender" id="gender" class="form-control @error('gender') is-invalid @enderror">
                      <option value="">Select Gender</option>
                      <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Male</option>
                      <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                    @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                </div>
                <!-- Nationality -->
                <div class="col-md-4">
                  <div class="mb-3">
                    <label for="nationality" class="form-label"><i class="fa fa-flag"></i> Nationality:</label>
                    <input type="text" name="nationality" id="nationality"
                      class="form-control @error('nationality') is-invalid @enderror"
                      value="{{ old('nationality', $user->nationality) }}" placeholder="Enter nationality">
                    @error('nationality') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                </div>
              </div>

              <!-- Skills -->
              <div class="col-md-12">
                <label for="skills" class="form-label"><i class="fa fa-code"></i> Skills:</label>
                <select name="skills[]" id="skills" class="form-select @error('skills') is-invalid @enderror" multiple>
                  @foreach($skills as $skill)
                    <option value="{{ $skill->id }}" {{ collect(old('skills', $user->skills->pluck('id')))->contains($skill->id) ? 'selected' : '' }}>
                      {{ $skill->name }}
                    </option>
                  @endforeach
                </select>
                @error('skills') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <!-- Introductory Video -->
              <div class="mb-3">
                <label for="video" class="form-label"><i class="fa fa-video"></i> Introductory Video:</label>
                <input type="file" name="video" id="video" accept="video/*" class="form-control @error('video') is-invalid @enderror">
                @error('video') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              
              <div class="mb-3">
                @if($user->video)
                  <video id="videoPreview" width="100%" height="auto" controls>
                    <source src="{{ asset('storage/' . $user->video) }}" type="video/mp4">
                    Your browser does not support the video tag.
                  </video>
                @else
                  <video id="videoPreview" width="100%" height="auto" controls style="display: none;"></video>
                @endif
              </div>
              
              <!-- Notes -->
              <div class="mb-3">
                <label for="notes" class="form-label"><i class="fa fa-sticky-note"></i> Notes:</label>
                <textarea name="notes" id="notes" rows="4" class="form-control @error('notes') is-invalid @enderror" placeholder="Enter any additional notes">{{ old('notes', $user->notes) }}</textarea>
                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            
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
                            value="{{ $permission->name }}"
                            {{ (is_array(old('permissions', $user->getPermissionNames()->toArray())) && in_array($permission->name, old('permissions', $user->getPermissionNames()->toArray()))) ? 'checked' : '' }}>
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
                <i class="fa fa-save"></i> Update User
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function () {
    // Initialize Select2 for the skills multiselect
    $('#skills').select2({
        placeholder: 'Select skills to develop',
        allowClear: true,
        width: '100%'
    });

    // Update permission groups based on selected roles
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

    // Listen for changes on all role checkboxes to update permissions
    document.querySelectorAll('.role-checkbox').forEach((checkbox) => {
      checkbox.addEventListener('change', function () {
        updatePermissions();
      });
    });

    // Initial check on load
    updatePermissions();

    // Video preview functionality
    document.getElementById("video").addEventListener("change", function (event) {
      var file = event.target.files[0];
      if (file) {
        var videoPreview = document.getElementById("videoPreview");
        videoPreview.src = URL.createObjectURL(file);
        videoPreview.style.display = "block";
      }
    });
  });
</script>
@endpush
