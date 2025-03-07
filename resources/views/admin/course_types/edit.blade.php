@extends('layouts.app')

@section('title', 'Edit Course Type')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
  <li class="breadcrumb-item">
    <a href="{{ route('admin.course-types.index') }}">
      <i class="fa fa-book"></i> Course Types
    </a>
  </li>
  <li class="breadcrumb-item active">
    <i class="fa fa-edit"></i> Edit Course Type
  </li>
@endsection

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header">
      <h4><i class="fa fa-edit"></i> Edit Course Type</h4>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.course-types.update', $courseType->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-3">
          <div class="col-md-6">
            <label for="name" class="form-label"><i class="fa fa-tag"></i> Name</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $courseType->name) }}" placeholder="Enter course type name">
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6">
            <label for="status" class="form-label"><i class="fa fa-toggle-on"></i> Status</label>
            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
              <option value="active" {{ old('status', $courseType->status) == 'active' ? 'selected' : '' }}>Active</option>
              <option value="inactive" {{ old('status', $courseType->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6">
            <label for="duration" class="form-label"><i class="fa fa-clock"></i> Duration</label>
            <select name="duration" id="duration" class="form-select @error('duration') is-invalid @enderror">
              <option value="">-- Select Duration --</option>
              <option value="week" {{ old('duration', $courseType->duration) == 'week' ? 'selected' : '' }}>Week</option>
              <option value="month" {{ old('duration', $courseType->duration) == 'month' ? 'selected' : '' }}>Month</option>
              <option value="half_year" {{ old('duration', $courseType->duration) == 'half_year' ? 'selected' : '' }}>Half Year</option>
            </select>
            @error('duration')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="mt-4 d-flex justify-content-end">
          <a href="{{ route('admin.course-types.index') }}" class="btn btn-outline-secondary me-2">
            <i class="fa fa-arrow-left"></i> Cancel
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Update Course Type
          </button>
        </div>
      </form>
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
