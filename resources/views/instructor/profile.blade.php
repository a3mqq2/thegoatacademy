@extends('layouts.app')

@section('title', 'My Profile')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('instructor.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
  <li class="breadcrumb-item">
    <a href="{{ route('instructor.profile') }}">
      <i class="fa fa-user"></i> My Profile
    </a>
  </li>
@endsection

@php
  $user = auth()->user();
  $daysList = ['Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday'];
@endphp

@section('content')
<form action="{{ route('instructor.profile.update') }}" method="POST" enctype="multipart/form-data">
  @csrf
  @method('PUT')

  <div class="card mt-3">
    <div class="card-body">

      {{-- Avatar --}}
      <div class="row mb-4">
        <div class="col-md-12 text-center">
          <img id="avatarPreview"
            src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/default-avatar.jpg') }}"
            alt="Avatar"
            class="rounded-circle"
            width="150" height="150"
            style="cursor:pointer; object-fit:cover;">
          <input type="file" name="avatar" id="avatarInput" class="d-none" accept="image/*">
          <div class="form-text mt-2"><i class="fa fa-image"></i> Click image to change or view full size</div>
          @error('avatar') <div class="text-danger"><i class="fa fa-exclamation-circle"></i> {{ $message }}</div> @enderror
        </div>
      </div>

      {{-- Info Fields --}}
      <div class="row">
        <div class="col-md-4 mb-3">
          <label><i class="fa fa-user"></i> Name</label>
          <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                 value="{{ old('name', $user->name) }}" required>
          @error('name') <div class="invalid-feedback"><i class="fa fa-exclamation-circle"></i> {{ $message }}</div> @enderror
        </div>
        <div class="col-md-4 mb-3">
          <label><i class="fa fa-phone"></i> Phone</label>
          <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                 value="{{ old('phone', $user->phone) }}" required>
          @error('phone') <div class="invalid-feedback"><i class="fa fa-exclamation-circle"></i> {{ $message }}</div> @enderror
        </div>
        <div class="col-md-4 mb-3">
          <label><i class="fa fa-envelope"></i> Email</label>
          <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                 value="{{ old('email', $user->email) }}" required>
          @error('email') <div class="invalid-feedback"><i class="fa fa-exclamation-circle"></i> {{ $message }}</div> @enderror
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label><i class="fa fa-lock"></i> New Password <small>(leave blank to keep current)</small></label>
          <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
          @error('password') <div class="invalid-feedback"><i class="fa fa-exclamation-circle"></i> {{ $message }}</div> @enderror
        </div>
        <div class="col-md-6 mb-3">
          <label><i class="fa fa-lock"></i> Confirm Password</label>
          <input type="password" name="password_confirmation" class="form-control">
        </div>
      </div>

      <div class="row">
        <div class="col-md-12 mb-3">
          <label><i class="fa fa-calendar"></i> Age</label>
          <input type="number" name="age" class="form-control @error('age') is-invalid @enderror"
                 value="{{ old('age', $user->age) }}">
          @error('age') <div class="invalid-feedback"><i class="fa fa-exclamation-circle"></i> {{ $message }}</div> @enderror
        </div>
      </div>

      <div class="mb-3">
        <label><i class="fa fa-video"></i> Introductory Video (YouTube Link)</label>
        <input type="text" name="video" class="form-control @error('video') is-invalid @enderror"
               value="{{ old('video', $user->video) }}" placeholder="https://youtube.com/...">
        @error('video') <div class="invalid-feedback"><i class="fa fa-exclamation-circle"></i> {{ $message }}</div> @enderror
      </div>

      <div class="mb-3">
        <label><i class="fa fa-sticky-note"></i> Notes</label>
        <textarea name="notes" rows="4" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $user->notes) }}</textarea>
        @error('notes') <div class="invalid-feedback"><i class="fa fa-exclamation-circle"></i> {{ $message }}</div> @enderror
      </div>

      {{-- Shifts --}}
      <div class="card mt-4">
        <div class="card-header bg-light text-primary">
          <h5><i class="fa fa-clock"></i> My Shifts</h5>
        </div>
        <div class="card-body">
          <div class="row g-3 align-items-end mb-3">
            <div class="col-md-6">
              <label><i class="fa fa-calendar-alt"></i> Days</label>
              <select id="daysSelect" class="form-select" multiple>
                @foreach($daysList as $day)
                  <option value="{{ $day }}">{{ $day }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label><i class="fa fa-clock"></i> From Time</label>
              <input type="time" id="from_time" class="form-control">
            </div>
            <div class="col-md-3">
              <label><i class="fa fa-clock"></i> To Time</label>
              <input type="time" id="to_time" class="form-control">
            </div>
          </div>

          <button type="button" class="btn btn-success mb-3" id="generateScheduleBtn">
            <i class="fa fa-plus-circle"></i> Add Shift(s)
          </button>

          <table class="table table-bordered" id="shiftsTable">
            <thead>
              <tr>
                <th><i class="fa fa-calendar-alt"></i> Day</th>
                <th><i class="fa fa-clock"></i> From</th>
                <th><i class="fa fa-clock"></i> To</th>
                <th><i class="fa fa-cog"></i> Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($user->shifts as $shift)
              <tr>
                <td>
                  <select name="shifts[][day]" class="form-control" required>
                    @foreach($daysList as $day)
                      <option value="{{ $day }}" {{ $day == $shift->day ? 'selected' : '' }}>{{ $day }}</option>
                    @endforeach
                  </select>
                </td>
                <td><input type="time" name="shifts[][start_time]" class="form-control" value="{{ $shift->start_time }}" required></td>
                <td><input type="time" name="shifts[][end_time]" class="form-control" value="{{ $shift->end_time }}" required></td>
                <td><button type="button" class="btn btn-danger btn-sm removeShiftBtn"><i class="fa fa-trash"></i></button></td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <div class="text-center mt-4">
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button>
      </div>

    </div>
  </div>
</form>
@endsection

@push('styles')
  {{-- FontAwesome --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-pX2Q2y...snipped..." crossorigin="anonymous" referrerpolicy="no-referrer" />
  {{-- Select2 --}}
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  {{-- FilePond --}}
  <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
  <link href="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.css" rel="stylesheet">
  <link href="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.css" rel="stylesheet">
  <link href="https://unpkg.com/filepond-plugin-media-preview/dist/filepond-plugin-media-preview.css" rel="stylesheet">
@endpush

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
  <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
  <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
  <script src="https://unpkg.com/filepond-plugin-media-preview/dist/filepond-plugin-media-preview.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Avatar preview & upload/view toggle
      const avatarPreview = document.getElementById('avatarPreview');
      const avatarInput = document.getElementById('avatarInput');
      avatarPreview.addEventListener('click', () => {
        if (confirm('Click OK to upload new avatar, Cancel to just view.')) {
          avatarInput.click();
        } else {
          window.open(avatarPreview.src, '_blank');
        }
      });
      avatarInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = e => avatarPreview.src = e.target.result;
          reader.readAsDataURL(file);
        }
      });

      // Select2 for Days
      $('#daysSelect').select2({ placeholder: 'Select days', width: '100%' });

      // Generate Shifts
      const shiftsTable = document.querySelector('#shiftsTable tbody');
      const daysSelect = document.getElementById('daysSelect');
      const fromTime = document.getElementById('from_time');
      const toTime = document.getElementById('to_time');
      document.getElementById('generateScheduleBtn').addEventListener('click', () => {
        const days = Array.from(daysSelect.selectedOptions).map(o => o.value);
        days.forEach(day => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>
              <select name="shifts[][day]" class="form-control" required>
                @foreach($daysList as $d)
                  <option value="{{ $d }}" ${day === '{{ $d }}' ? 'selected' : ''}>{{ $d }}</option>
                @endforeach
              </select>
            </td>
            <td><input type="time" name="shifts[][start_time]" class="form-control" value="${fromTime.value}" required></td>
            <td><input type="time" name="shifts[][end_time]" class="form-control" value="${toTime.value}" required></td>
            <td><button type="button" class="btn btn-danger btn-sm removeShiftBtn"><i class="fa fa-trash"></i></button></td>
          `;
          shiftsTable.appendChild(tr);
        });
      });
      shiftsTable.addEventListener('click', e => {
        if (e.target.closest('.removeShiftBtn')) {
          e.target.closest('tr').remove();
        }
      });
    });
  </script>
@endpush
