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

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header">
      <h4><i class="fa fa-plus"></i> Create Student</h4>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.students.store') }}" method="POST">
        @csrf
        <div class="row g-3">
          <div class="col-md-6">
            <label for="name" class="form-label"><i class="fa fa-user"></i> Name</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Enter student name" required>
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label for="phone" class="form-label"><i class="fa fa-phone"></i> Phone</label>
            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="Enter phone number" required>
            @error('phone')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>


        
          <div class="col-md-12">
            <label for="books_due" class="form-label"><i class="fa fa-book"></i> Books Due</label>
            <select name="books_due" id="books_due" class="form-select @error('books_due') is-invalid @enderror" required>
              <option value="0" {{ old('books_due') == '0' ? 'selected' : '' }}>No</option>
              <option value="1" {{ old('books_due') == '1' ? 'selected' : '' }}>Yes</option>
            </select>
            @error('books_due')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
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
<script>
  document.addEventListener("DOMContentLoaded", function () {
    let statusSelect = document.getElementById("status");
    let withdrawalReasonField = document.getElementById("withdrawalReasonField");

    function toggleWithdrawalReason() {
      if (statusSelect.value === "withdrawn") {
        withdrawalReasonField.style.display = "block";
      } else {
        withdrawalReasonField.style.display = "none";
      }
    }

    statusSelect.addEventListener("change", toggleWithdrawalReason);
    toggleWithdrawalReason();
  });
</script>
@endpush
