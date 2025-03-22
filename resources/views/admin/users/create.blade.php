{{-- resources/views/admin/users/create.blade.php --}}
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
          <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            {{-- Basic Info --}}
            <div class="row">
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="name" class="form-label"><i class="fa fa-user"></i> Name:</label>
                  <input type="text" name="name" id="name"
                         class="form-control @error('name') is-invalid @enderror"
                         value="{{ old('name') }}" placeholder="Enter full name" required>
                  @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="phone" class="form-label"><i class="fa fa-phone"></i> Phone:</label>
                  <input type="text" name="phone" id="phone"
                         class="form-control @error('phone') is-invalid @enderror"
                         value="{{ old('phone') }}" placeholder="Enter phone" required>
                  @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="email" class="form-label"><i class="fa fa-envelope"></i> Email:</label>
                  <input type="email" name="email" id="email"
                         class="form-control @error('email') is-invalid @enderror"
                         value="{{ old('email') }}" placeholder="Enter email" required>
                  @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>
            </div>
            
            {{-- Password Fields --}}
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
            </div>
            
            {{-- Avatar + Cost per hour --}}
            <div class="row mb-2">
              <div class="col-md-6">
                <label for="avatar">Avatar</label>
                <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror">
                @error('avatar') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="cost_per_hour" class="form-label"><i class="fa fa-money-bill-wave"></i> Cost Per Hour:</label>
                  <input type="number" name="cost_per_hour" id="cost_per_hour"
                         class="form-control @error('cost_per_hour') is-invalid @enderror"
                         value="{{ old('cost_per_hour') }}" placeholder="Enter cost per hour" required>
                  @error('cost_per_hour') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>
            </div>

            {{-- Roles --}}
            <div class="row">
              <div class="col-md-12">
                <label class="form-label"><i class="fa fa-user-shield"></i> Assign Roles:</label>
                <div class="mb-3 border p-3 rounded">
                  @foreach($roles as $role)
                    <div class="form-check form-switch">
                      <input class="form-check-input role-checkbox" type="checkbox"
                             name="roles[]" id="role_{{ $role->name }}" value="{{ $role->name }}"
                             data-role-id="{{ $role->name }}"
                             {{ (is_array(old('roles')) && in_array($role->name, old('roles'))) ? 'checked' : '' }}>
                      <label class="form-check-label" for="role_{{ $role->name }}">{{ ucfirst($role->name) }}</label>
                    </div>
                  @endforeach
                  @error('roles') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
              </div>
            </div>
            
            {{-- Instructor Fields --}}
            <div id="instructorFields">
              <div class="row">
                <div class="col-md-4">
                  <div class="mb-3">
                    <label for="age" class="form-label"><i class="fa fa-calendar"></i> Age:</label>
                    <input type="number" name="age" id="age"
                           class="form-control @error('age') is-invalid @enderror"
                           value="{{ old('age') }}" placeholder="Enter age">
                    @error('age') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label for="gender" class="form-label"><i class="fa fa-venus-mars"></i> Gender:</label>
                    <select name="gender" id="gender" class="form-control @error('gender') is-invalid @enderror">
                      <option value="">Select Gender</option>
                      <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                      <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                    @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label for="nationality" class="form-label"><i class="fa fa-flag"></i> Nationality:</label>
                    <input type="text" name="nationality" id="nationality"
                           class="form-control @error('nationality') is-invalid @enderror"
                           value="{{ old('nationality') }}" placeholder="Enter nationality">
                    @error('nationality') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                </div>
              </div>
              
              {{-- Skills & Levels --}}
              <div class="row">
                <div class="col-md-12">
                  <label for="skills" class="form-label"><i class="fa fa-code"></i> Skills:</label>
                  <select name="skills[]" id="skills" class="form-select @error('skills') is-invalid @enderror" multiple>
                    @foreach($skills as $skill)
                      <option value="{{ $skill->id }}" {{ $skill->name != "pronunciation" ? "selected" : "" }}>
                        {{ $skill->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('skills') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-12 mt-3">
                  <label for="levels" class="form-label"><i class="fa fa-code"></i> Levels:</label>
                  <select name="levels[]" id="levels" class="form-select @error('levels') is-invalid @enderror" multiple>
                    @foreach($levels as $level)
                      <option value="{{ $level->id }}" {{ $level->name != "pronunciation" ? "selected" : "" }}>
                        {{ $level->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('levels') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>
              
              {{-- Introductory Video --}}
              <div class="mb-3 mt-3">
                <label for="video" class="form-label"><i class="fa fa-video"></i> Introductory Video (Youtube Link) :</label>
                {{-- <input type="file" name="video" id="video" accept="video/*"
                       class="filepond @error('video') is-invalid @enderror">
                @error('video') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <input type="hidden" name="video_path" id="video_path" value=""> --}}

                <input type="text" placeholder="Enter Youtube Link here" name="video" class="form-control">
              </div>
              
              <div class="mb-3">
                <video id="videoPreview" width="100%" height="auto" controls style="display: none;"></video>
              </div>
              
              {{-- Notes --}}
              <div class="mb-3">
                <label for="notes" class="form-label"><i class="fa fa-sticky-note"></i> Notes:</label>
                <textarea name="notes" id="notes" rows="4"
                          class="form-control @error('notes') is-invalid @enderror"
                          placeholder="Enter any additional notes">{{ old('notes') }}</textarea>
                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            
            {{-- User Shifts Section --}}
            <div class="card mt-4">
              <div class="card-header bg-light text-primary">
                <h5 class="mb-0 text-primary"><i class="fa fa-clock"></i> User Shifts</h5>
              </div>
              <div class="card-body">
                {{-- Multi-select days + from/to time + Generate button --}}
                <div class="row g-3 align-items-end mb-3">
                  <div class="col-md-6">
                    <label for="daysSelect" class="form-label"><strong>Days</strong></label>
                    {{-- Select2-Enabled Multi-Select for Days --}}
                    <select id="daysSelect" class="form-select" multiple>
                      <option value="Saturday">Saturday</option>
                      <option value="Sunday">Sunday</option>
                      <option value="Monday">Monday</option>
                      <option value="Tuesday">Tuesday</option>
                      <option value="Wednesday">Wednesday</option>
                      <option value="Thursday">Thursday</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label for="from_time" class="form-label"><strong>From Time</strong></label>
                    <input type="time" id="from_time" class="form-control">
                  </div>
                  <div class="col-md-3">
                    <label for="to_time" class="form-label"><strong>To Time</strong></label>
                    <input type="time" id="to_time" class="form-control">
                  </div>
                </div>
                <button type="button" class="btn btn-primary mb-3" id="generateScheduleBtn">
                  <i class="fa fa-calendar-plus"></i> Generate Schedule
                </button>

                {{-- Table of Shifts --}}
                <table class="table" id="shiftsTable">
                  <thead>
                    <tr>
                      <th>Day</th>
                      <th>From Time</th>
                      <th>To Time</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    {{-- Rows appended via JavaScript --}}
                  </tbody>
                </table>
                
                {{-- Optional single-shift add button (manually) --}}
                <button type="button" class="btn btn-success" id="addShiftBtn">
                  <i class="fa fa-plus"></i> Add Single Shift
                </button>
              </div>
            </div>
            
            {{-- Permissions Section --}}
            <div class="row mt-3">
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
            </div>
            
            {{-- Submit --}}
            <div class="text-end">
              <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Cancel
              </a>
              <button id="submitBtn" type="submit" class="btn btn-primary">
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

@push('styles')
  {{-- Select2 --}}
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  {{-- FilePond core CSS --}}
  <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
  {{-- FilePond plugins CSS (optional) --}}
  <link href="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.css" rel="stylesheet">
  <link href="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.css" rel="stylesheet">
  <link href="https://unpkg.com/filepond-plugin-media-preview/dist/filepond-plugin-media-preview.css" rel="stylesheet">
@endpush

@push('scripts')
  {{-- Select2 --}}
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  {{-- FilePond core JS + plugins --}}
  <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
  <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
  <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
  <script src="https://unpkg.com/filepond-plugin-media-preview/dist/filepond-plugin-media-preview.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const daysList = ["Saturday","Sunday","Monday","Tuesday","Wednesday","Thursday"];

      function updatePermissions() {
        let selectedRoles = [];
        document.querySelectorAll('.role-checkbox:checked').forEach(el => {
          selectedRoles.push(el.dataset.roleId);
        });
        document.querySelectorAll('.permissions-group').forEach(permGroup => {
          let roleId = permGroup.dataset.roleId;
          permGroup.style.display = selectedRoles.includes(roleId) ? "block" : "none";
        });
      }
      document.querySelectorAll('.role-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', () => {
          updatePermissions();
        });
      });
      updatePermissions();

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
      $('#daysSelect').select2({
        placeholder: 'Select days',
        width: '100%'
      });

      FilePond.registerPlugin(
        FilePondPluginFileValidateType,
        FilePondPluginFileValidateSize,
        FilePondPluginMediaPreview
      );

      const videoInputElement = document.querySelector('#video');
      FilePond.create(videoInputElement, {
        server: {
          process: {
            url: '{{ route("upload.file") }}',
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            timeout: 10 * 60 * 1000
          },
          revert: {
            url: '{{ route("upload.file.revert") }}',
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
          }
        },
        acceptedFileTypes: ['video/*'],
        chunkUploads: false,
        chunkSize: 2 * 1024 * 1024, 
        chunkRetryDelays: [500, 1000, 3000],
        onprocessfilestart: () => {
          document.getElementById('submitBtn').disabled = true;
        },
        onprocessfile: (error, file) => {
          document.getElementById('submitBtn').disabled = false;
          if (!error) {
            let serverResponse = file.serverId;
            try {
              serverResponse = JSON.parse(file.serverId);
            } catch (e) {}
            document.getElementById('video_path').value = serverResponse.path;
          }
        },
        onprocessfileabort: () => {
          document.getElementById('submitBtn').disabled = false;
        },
        onprocessfileerror: () => {
          document.getElementById('submitBtn').disabled = false;
        },
        ondata: (formData) => {
          const file = formData.get('file');
          formData.delete('file');
          formData.append('video', file);
          return formData;
        }
      });

      const shiftsTableBody = document.querySelector("#shiftsTable tbody");
      const addShiftBtn = document.getElementById("addShiftBtn");
      const generateScheduleBtn = document.getElementById("generateScheduleBtn");
      const fromTimeEl = document.getElementById("from_time");
      const toTimeEl = document.getElementById("to_time");
      const daysSelect = document.getElementById("daysSelect");

      function createDaySelect(selectedDay = "") {
        let optionsHtml = daysList.map(day => {
          const isSelected = (day === selectedDay) ? 'selected' : '';
          return `<option value="${day}" ${isSelected}>${day}</option>`;
        }).join("");
        return `
          <select name="shifts[][day]" class="form-control" required>
            ${optionsHtml}
          </select>
        `;
      }

      generateScheduleBtn.addEventListener("click", () => {
        const fromTime = fromTimeEl.value;
        const toTime = toTimeEl.value;
        const selectedDays = Array.from(daysSelect.selectedOptions).map(option => option.value);

        selectedDays.forEach(day => {
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td>${createDaySelect(day)}</td>
            <td>
              <input type="time" name="shifts[][start_time]" class="form-control" value="${fromTime}" required>
            </td>
            <td>
              <input type="time" name="shifts[][end_time]" class="form-control" value="${toTime}" required>
            </td>
            <td>
              <button type="button" class="btn btn-danger btn-sm removeShiftBtn">
                <i class="fa fa-trash"></i>
              </button>
            </td>
          `;
          shiftsTableBody.appendChild(tr);
        });
      });

      addShiftBtn.addEventListener("click", () => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${createDaySelect("Saturday")}</td>
          <td>
            <input type="time" name="shifts[][from_time]" class="form-control" required>
          </td>
          <td>
            <input type="time" name="shifts[][to_time]" class="form-control" required>
          </td>
          <td>
            <button type="button" class="btn btn-danger btn-sm removeShiftBtn">
              <i class="fa fa-trash"></i>
            </button>
          </td>
        `;
        shiftsTableBody.appendChild(tr);
      });

      shiftsTableBody.addEventListener("click", (e) => {
        if (e.target.closest(".removeShiftBtn")) {
          e.target.closest("tr").remove();
        }
      });
    });
  </script>
@endpush
