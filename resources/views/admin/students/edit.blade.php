@extends('layouts.app')

@section('title', 'Edit Student')

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
    <i class="fa fa-edit"></i> Edit Student
  </li>
@endsection

@push('styles')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header">
      <h4><i class="fa fa-edit"></i> Edit Student</h4>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.students.update', $student->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label"><i class="fa fa-user"></i> Name</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $student->name) }}" required>
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label"><i class="fa fa-phone"></i> Phone</label>
            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $student->phone) }}" required>
            @error('phone')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label"><i class="fa fa-city"></i> City</label>
            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $student->city) }}">
            @error('city')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label"><i class="fa fa-birthday-cake"></i> Age</label>
            <input type="number" name="age" class="form-control @error('age') is-invalid @enderror" value="{{ old('age', $student->age) }}">
            @error('age')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label"><i class="fa fa-graduation-cap"></i> Specialization</label>
            <input type="text" name="specialization" class="form-control @error('specialization') is-invalid @enderror" value="{{ old('specialization', $student->specialization) }}">
            @error('specialization')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label"><i class="fa fa-venus-mars"></i> Gender</label>
            <select name="gender" class="form-select @error('gender') is-invalid @enderror">
              <option value="male" {{ old('gender', $student->gender) == 'male' ? 'selected' : '' }}>Male</option>
              <option value="female" {{ old('gender', $student->gender) == 'female' ? 'selected' : '' }}>Female</option>
            </select>
            @error('gender')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label"><i class="fa fa-image"></i> Avatar</label>
            <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror">
            @error('avatar')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label"><i class="fa fa-phone-alt"></i> Emergency Phone</label>
            <input type="text" name="emergency_phone" class="form-control @error('emergency_phone') is-invalid @enderror" value="{{ old('emergency_phone', $student->emergency_phone) }}">
            @error('emergency_phone')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Multiselect for Skills to Develop using Select2 -->
          <div class="col-md-12">
            <label for="skills" class="form-label"><i class="fa fa-code"></i> Skills to Develop</label>
            <select name="skills[]" id="skills" class="form-select @error('skills') is-invalid @enderror" multiple>
              @foreach($skills as $skill)
                <option value="{{ $skill->id }}" {{ collect(old('skills', $student->skills->pluck('id')->toArray()))->contains($skill->id) ? 'selected' : '' }}>
                  {{ $skill->name }}
                </option>
              @endforeach
            </select>
            @error('skills')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="mt-4 d-flex justify-content-end">
          <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary me-2">
            <i class="fa fa-arrow-left"></i> Cancel
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Update Student
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
        placeholder: 'Select skills to develop',
        allowClear: true
      });
    });
  </script>
@endpush
