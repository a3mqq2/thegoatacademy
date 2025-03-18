@extends('layouts.app')

@section('title', 'Create Student')

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
    <i class="fa fa-plus"></i> Create Student
  </li>
@endsection

@push('styles')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header">
      <h4><i class="fa fa-plus"></i> Create Student</h4>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
          <div class="col-md-6">
            <label for="name" class="form-label"><i class="fa fa-user"></i> Name</label>
            <input type="text" name="name" id="name" 
                   class="form-control @error('name') is-invalid @enderror" 
                   value="{{ old('name') }}" placeholder="Enter student name" required>
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label for="phone" class="form-label"><i class="fa fa-phone"></i> Phone</label>
            <input type="text" name="phone" id="phone" 
                   class="form-control @error('phone') is-invalid @enderror" 
                   value="{{ old('phone') }}" placeholder="Enter phone number" required>
            @error('phone')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label for="city" class="form-label"><i class="fa fa-city"></i> City</label>
            <input type="text" name="city" id="city" 
                   class="form-control @error('city') is-invalid @enderror" 
                   value="{{ old('city') }}" placeholder="Enter city">
            @error('city')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label for="age" class="form-label"><i class="fa fa-birthday-cake"></i> Age</label>
            <input type="number" name="age" id="age" 
                   class="form-control @error('age') is-invalid @enderror" 
                   value="{{ old('age') }}" placeholder="Enter age">
            @error('age')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label for="specialization" class="form-label">
              <i class="fa fa-graduation-cap"></i> Specialization
            </label>
            <input type="text" name="specialization" id="specialization" 
                   class="form-control @error('specialization') is-invalid @enderror" 
                   value="{{ old('specialization') }}" placeholder="Enter specialization">
            @error('specialization')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label for="gender" class="form-label"><i class="fa fa-venus-mars"></i> Gender</label>
            <select name="gender" id="gender" 
                    class="form-select @error('gender') is-invalid @enderror">
              <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
              <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
            </select>
            @error('gender')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label for="avatar" class="form-label"><i class="fa fa-image"></i> Avatar</label>
            <input type="file" name="avatar" id="avatar" 
                   class="form-control @error('avatar') is-invalid @enderror">
            @error('avatar')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label for="emergency_phone" class="form-label"><i class="fa fa-phone-alt"></i> Emergency Phone</label>
            <input type="text" name="emergency_phone" id="emergency_phone" 
                   class="form-control @error('emergency_phone') is-invalid @enderror" 
                   value="{{ old('emergency_phone') }}" placeholder="Enter emergency phone">
            @error('emergency_phone')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Multiselect for Skills using Select2 -->
          <div class="col-md-12">
            <label for="skills" class="form-label"><i class="fa fa-code"></i> Skills to Develop</label>
            <select name="skills[]" id="skills" 
                    class="form-select @error('skills') is-invalid @enderror" multiple>
              @foreach($skills as $skill)
                <option value="{{ $skill->id }}" 
                  selected>
                  {{ $skill->name }}
                </option>
              @endforeach
            </select>
            @error('skills')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <!-- Container for Suggested Courses -->
        <div id="suggested-courses" class="mt-4"></div>

        <!-- Multiple File Upload Table -->
        <div class="card mt-4">
          <div class="card-header">
            <h5><i class="fa fa-folder-open"></i> Upload Multiple Files</h5>
          </div>
          <div class="card-body">
            <table class="table" id="filesTable">
              <thead>
                <tr>
                  <th>File Name</th>
                  <th>File</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <!-- Rows are dynamically added here -->
              </tbody>
            </table>
            <button type="button" class="btn btn-success" id="addFileRow">
              <i class="fa fa-plus"></i> Add File
            </button>
          </div>
        </div>

        <div class="mt-4 d-flex justify-content-end">
          <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary me-2">
            <i class="fa fa-arrow-left"></i> Cancel
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Save Student
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#skills').select2({
        placeholder: 'Select skills',
        allowClear: true
      });

      $('#skills').on('change', function() {
        var selectedSkills = $(this).val();
        $.ajax({
          url: "{{ route('courses.suggestions') }}", 
          method: "GET",
          data: { skills: selectedSkills },
          beforeSend: function() {
            $('#suggested-courses').html('<div class="alert alert-info">Loading suggested courses...</div>');
          },
          success: function(response) {
            $('#suggested-courses').html(response);
          },
          error: function(xhr, status, error) {
            console.error(error);
            $('#suggested-courses').html('<div class="alert alert-danger">Unable to load suggested courses at this time.</div>');
          }
        });
      });

      // Dynamic row addition for multiple files
      $('#addFileRow').on('click', function() {
        let row = `
          <tr>
            <td>
              <input type="text" name="file_names[]" class="form-control" placeholder="File Name" required>
            </td>
            <td>
              <input type="file" name="files[]" class="form-control" required>
            </td>
            <td>
              <button type="button" class="btn btn-danger removeRow">
                <i class="fa fa-trash"></i>
              </button>
            </td>
          </tr>
        `;
        $('#filesTable tbody').append(row);
      });

      $(document).on('click', '.removeRow', function() {
        $(this).closest('tr').remove();
      });
    });
  </script>
@endpush
