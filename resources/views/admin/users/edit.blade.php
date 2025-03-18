@extends('layouts.app')

@section('title', 'Edit User')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
  <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
  <li class="breadcrumb-item active">Edit User</li>
@endsection

@section('content')
<div class="container">
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
            
            <!-- Basic Information -->
            <div class="row">
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="name" class="form-label"><i class="fa fa-user"></i> Name:</label>
                  <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" placeholder="Enter full name" required>
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="phone" class="form-label"><i class="fa fa-phone"></i> Phone:</label>
                  <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" placeholder="Enter phone" required>
                  @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="email" class="form-label"><i class="fa fa-envelope"></i> Email:</label>
                  <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" placeholder="Enter email" required>
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <!-- Password (Optional) -->
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="password" class="form-label"><i class="fa fa-lock"></i> Password (optional):</label>
                  <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter new password (if changing)">
                  @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="password_confirmation" class="form-label"><i class="fa fa-lock"></i> Confirm Password:</label>
                  <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Confirm new password">
                </div>
              </div>
            </div>

            <!-- Avatar & Cost Per Hour -->
            <div class="row mb-2">
              <div class="col-md-6">
                <label for="">Avatar</label>
                <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror">
                @error('avatar')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @if($user->avatar)
                  <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="rounded-circle mt-2" width="100">
                @endif
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="cost_per_hour" class="form-label"><i class="fa fa-money-bill-wave"></i> Cost Per Hour:</label>
                  <input type="number" name="cost_per_hour" id="cost_per_hour" class="form-control @error('cost_per_hour') is-invalid @enderror" value="{{ old('cost_per_hour', $user->cost_per_hour) }}" placeholder="Enter cost per hour" required>
                  @error('cost_per_hour')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <!-- Roles Section -->
            <div class="row">
              <div class="col-md-12">
                <label class="form-label"><i class="fa fa-user-shield"></i> Assign Roles:</label>
                <div class="mb-3 border p-3 rounded">
                  @foreach($roles as $role)
                    <div class="form-check form-switch">
                      <input class="form-check-input role-checkbox" type="checkbox" name="roles[]" id="role_{{ $role->name }}" value="{{ $role->name }}" data-role-id="{{ $role->name }}"
                      {{ (is_array(old('roles', $user->getRoleNames()->toArray())) && in_array($role->name, old('roles', $user->getRoleNames()->toArray()))) ? 'checked' : '' }}>
                      <label class="form-check-label" for="role_{{ $role->name }}">{{ ucfirst($role->name) }}</label>
                    </div>
                  @endforeach
                  @error('roles')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <!-- Additional Trainer Fields -->
            <div id="instructorFields">
              <div class="row">
                <!-- Age -->
                <div class="col-md-4">
                  <div class="mb-3">
                    <label for="age" class="form-label"><i class="fa fa-calendar"></i> Age:</label>
                    <input type="number" name="age" id="age" class="form-control @error('age') is-invalid @enderror" value="{{ old('age', $user->age) }}" placeholder="Enter age">
                    @error('age')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <!-- Gender -->
                <div class="col-md-4">
                  <div class="mb-3">
                    <label for="gender" class="form-label"><i class="fa fa-venus-mars"></i> Gender:</label>
                    <select name="gender" id="gender" class="form-control @error('gender') is-invalid @enderror">
                      <option value="">Select Gender</option>
                      <option value="male" @if(old('gender', $user->gender) == 'male') selected @endif>Male</option>
                      <option value="female" @if(old('gender', $user->gender) == 'female') selected @endif>Female</option>
                    </select>
                    @error('gender')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <!-- Nationality -->
                <div class="col-md-4">
                  <div class="mb-3">
                    <label for="nationality" class="form-label"><i class="fa fa-flag"></i> Nationality:</label>
                    <input type="text" name="nationality" id="nationality" class="form-control @error('nationality') is-invalid @enderror" value="{{ old('nationality', $user->nationality) }}" placeholder="Enter nationality">
                    @error('nationality')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>

              <!-- Skills & Levels Multiselect -->
              <div class="row">
                <div class="col-md-12">
                  <label for="skills" class="form-label"><i class="fa fa-code"></i> Skills:</label>
                  <select name="skills[]" id="skills" class="form-select @error('skills') is-invalid @enderror" multiple>
                    @foreach($skills as $skill)
                      <option value="{{ $skill->id }}" @if($user->skills->contains($skill->id)) selected @endif>
                        {{ $skill->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('skills')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-12 mt-2">
                  <label for="levels" class="form-label"><i class="fa fa-code"></i> Levels:</label>
                  <select name="levels[]" id="levels" class="form-select @error('levels') is-invalid @enderror" multiple>
                    @foreach($levels as $level)
                      <option value="{{ $level->id }}" @if($user->levels->contains($level->id)) selected @endif>
                        {{ $level->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('levels')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Introductory Video -->
              <div class="mb-3 mt-3">
                <label for="video" class="form-label"><i class="fa fa-video"></i> Introductory Video:</label>
                <input type="file" name="video" id="video" accept="video/*" class="filepond @error('video') is-invalid @enderror">
                @error('video')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <input type="hidden" name="video_path" id="video_path" value="{{ old('video_path', $user->video) }}">
              </div>
              <div class="mb-3">
                @if($user->video)
                  <video src="{{ asset('storage/' . $user->video) }}" width="100%" height="auto" controls></video>
                @endif
              </div>

              <!-- Notes -->
              <div class="mb-3">
                <label for="notes" class="form-label"><i class="fa fa-sticky-note"></i> Notes:</label>
                <textarea name="notes" id="notes" rows="4" class="form-control @error('notes') is-invalid @enderror" placeholder="Enter any additional notes">{{ old('notes', $user->notes) }}</textarea>
                @error('notes')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- User Shifts Section -->
            <div class="card mt-4">
              <div class="card-header bg-light text-primary">
                <h5 class="mb-0 text-primary"><i class="fa fa-clock"></i> User Shifts</h5>
              </div>
              <div class="card-body">
                <table class="table" id="shiftsTable">
                  <thead>
                    <tr>
                      <th>Shift Day</th>
                      <th>Start Time</th>
                      <th>End Time</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($user->shifts->isNotEmpty())
                      @foreach($user->shifts as $index => $shift)
                        <tr>
                          <td>
                            <select name="shifts[{{ $index }}][day]" class="form-control" required>
                              <option value="Saturday" {{ $shift->day == 'Saturday' ? 'selected' : '' }}>Saturday</option>
                              <option value="Sunday" {{ $shift->day == 'Sunday' ? 'selected' : '' }}>Sunday</option>
                              <option value="Monday" {{ $shift->day == 'Monday' ? 'selected' : '' }}>Monday</option>
                              <option value="Tuesday" {{ $shift->day == 'Tuesday' ? 'selected' : '' }}>Tuesday</option>
                              <option value="Wednesday" {{ $shift->day == 'Wednesday' ? 'selected' : '' }}>Wednesday</option>
                              <option value="Thursday" {{ $shift->day == 'Thursday' ? 'selected' : '' }}>Thursday</option>
                              <option value="Friday" {{ $shift->day == 'Friday' ? 'selected' : '' }}>Friday</option>
                            </select>
                          </td>
                          <td>
                            <input type="time" name="shifts[{{ $index }}][start_time]" class="form-control" value="{{ $shift->start_time }}" required>
                          </td>
                          <td>
                            <input type="time" name="shifts[{{ $index }}][end_time]" class="form-control" value="{{ $shift->end_time }}" required>
                          </td>
                          <td>
                            <button type="button" class="btn btn-danger btn-sm removeShiftBtn">
                              <i class="fa fa-trash"></i>
                            </button>
                          </td>
                        </tr>
                      @endforeach
                    @endif
                  </tbody>
                </table>
                <button type="button" class="btn btn-success" id="addShiftBtn">
                  <i class="fa fa-plus"></i> Add Shift
                </button>
              </div>
            </div>

            <!-- Permissions Section -->
            <div class="row mt-4">
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
            </div>

            <div class="text-end mt-3">
              <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Cancel
              </a>
              <button id="submitBtn" type="submit" class="btn btn-primary">
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
  <!-- Select2 & FilePond CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
@endpush

@push('scripts')
  <!-- Select2 & FilePond JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {

      // Function to update indices for shift rows
      function updateShiftIndices() {
        const rows = document.querySelectorAll("#shiftsTable tbody tr");
        rows.forEach((tr, index) => {
          // Update the name attributes for each input/select
          tr.querySelectorAll("select, input").forEach(input => {
            // Get the base name without index (e.g., "shifts[][day]" -> "shifts")
            let base = input.getAttribute("name").replace(/shifts\[\d*\]/, "shifts[" + index + "]");
            input.setAttribute("name", base);
          });
        });
      }

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

      document.querySelectorAll('.role-checkbox').forEach((checkbox) => {
        checkbox.addEventListener('change', function () {
          updatePermissions();
        });
      });
      updatePermissions();

      // Initialize Select2 for skills and levels
      $('#skills').select2({
        placeholder: 'Select skills',
        allowClear: true,
        width: '100%'
      });
      $('#levels').select2({
        placeholder: 'Select levels',
        allowClear: true,
        width: '100%'
      });

      // Initialize FilePond for video
      const videoInputElement = document.querySelector('input[id="video"]');
      FilePond.create(videoInputElement, {
        server: {
          process: {
            url: '{{ route("upload.file") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
          },
          revert: {
            url: '{{ route("upload.file.revert") }}',
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
          }
        },
        acceptedFileTypes: ['video/*'],
        onprocessfilestart: () => { document.getElementById('submitBtn').disabled = true; },
        onprocessfile: (error, file) => {
          document.getElementById('submitBtn').disabled = false;
          if (!error) {
            let serverResponse = file.serverId;
            try { serverResponse = JSON.parse(file.serverId); } catch(e) {}
            document.getElementById('video_path').value = serverResponse.path;
          }
        },
        onprocessfileabort: () => { document.getElementById('submitBtn').disabled = false; },
        onprocessfileerror: () => { document.getElementById('submitBtn').disabled = false; }
      });

      // User Shifts Dynamic Rows
      const shiftsTableBody = document.querySelector("#shiftsTable tbody");
      const addShiftBtn = document.getElementById("addShiftBtn");

      addShiftBtn.addEventListener("click", function () {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>
            <select name="shifts[][day]" class="form-control" required>
              <option value="Saturday">Saturday</option>
              <option value="Sunday">Sunday</option>
              <option value="Monday">Monday</option>
              <option value="Tuesday">Tuesday</option>
              <option value="Wednesday">Wednesday</option>
              <option value="Thursday">Thursday</option>
              <option value="Friday">Friday</option>
            </select>
          </td>
          <td>
            <input type="time" name="shifts[][start_time]" class="form-control" required>
          </td>
          <td>
            <input type="time" name="shifts[][end_time]" class="form-control" required>
          </td>
          <td>
            <button type="button" class="btn btn-danger btn-sm removeShiftBtn">
              <i class="fa fa-trash"></i>
            </button>
          </td>
        `;
        shiftsTableBody.appendChild(tr);
        updateShiftIndices();
      });

      // Delegate deletion and update indices afterwards
      shiftsTableBody.addEventListener("click", function (e) {
        if (e.target.closest(".removeShiftBtn")) {
          e.target.closest("tr").remove();
          updateShiftIndices();
        }
      });
    });
  </script>
@endpush
