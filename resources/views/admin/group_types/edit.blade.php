@extends('layouts.app')

@section('title', 'Edit Group Type')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
  <li class="breadcrumb-item">
    <a href="{{ route('admin.group-types.index') }}">
      <i class="fa fa-users"></i> Group Types
    </a>
  </li>
  <li class="breadcrumb-item active">
    <i class="fa fa-edit"></i> Edit Group Type
  </li>
@endsection

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header">
      <h4><i class="fa fa-edit"></i> Edit Group Type</h4>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.group-types.update', $groupType->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-3">
          <div class="col-md-6">
            <label for="name" class="form-label"><i class="fa fa-tag"></i> Name</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $groupType->name) }}" placeholder="Enter group type name">
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label for="student_capacity" class="form-label"><i class="fa fa-user-graduate"></i> Student Capacity</label>
            <input type="number" name="student_capacity" id="student_capacity" class="form-control @error('student_capacity') is-invalid @enderror" value="{{ old('student_capacity', $groupType->student_capacity) }}" placeholder="Enter student capacity" min="1">
            @error('student_capacity')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label for="lesson_duration" class="form-label"><i class="fa fa-clock"></i> Lesson Duration In (Minutes)</label>
            <input type="number" name="lesson_duration" id="lesson_duration" class="form-control @error('lesson_duration') is-invalid @enderror" value="{{ old('lesson_duration', $groupType->lesson_duration) }}">
            @error('lesson_duration')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label for="status" class="form-label"><i class="fa fa-toggle-on"></i> Status</label>
            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
              <option value="active" {{ old('status', $groupType->status) == 'active' ? 'selected' : '' }}>Active</option>
              <option value="inactive" {{ old('status', $groupType->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        
        <div class="mt-4 d-flex justify-content-end">
          <a href="{{ route('admin.group-types.index') }}" class="btn btn-outline-secondary me-2">
            <i class="fa fa-arrow-left"></i> Cancel
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Update Group Type
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
