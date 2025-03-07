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

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header">
      <h4><i class="fa fa-edit"></i> Edit Student</h4>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.students.update', $student->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-3">
          <div class="col-md-6">
            <label for="name" class="form-label"><i class="fa fa-user"></i> Name</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $student->name) }}" placeholder="Enter student name" required>
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label for="phone" class="form-label"><i class="fa fa-phone"></i> Phone</label>
            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $student->phone) }}" placeholder="Enter phone number" required>
            @error('phone')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          {{-- <div class="col-md-6">
            <label for="status" class="form-label"><i class="fa fa-toggle-on"></i> Status</label>
            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
              <option value="ongoing" {{ old('status', $student->status) == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
              <option value="excluded" {{ old('status', $student->status) == 'excluded' ? 'selected' : '' }}>Excluded</option>
              <option value="withdrawn" {{ old('status', $student->status) == 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
            </select>
            @error('status')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div> --}}

          <div class="col-md-6" id="withdrawalReasonField" style="{{ old('status', $student->status) == 'withdrawn' ? 'display:block' : 'display:none' }}">
            <label for="withdrawal_reason" class="form-label"><i class="fa fa-comment"></i> Withdrawal Reason</label>
            <input type="text" name="withdrawal_reason" id="withdrawal_reason" class="form-control @error('withdrawal_reason') is-invalid @enderror" value="{{ old('withdrawal_reason', $student->withdrawal_reason) }}" placeholder="Enter reason">
            @error('withdrawal_reason')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label for="books_due" class="form-label"><i class="fa fa-book"></i> Books Due</label>
            <select name="books_due" id="books_due" class="form-select @error('books_due') is-invalid @enderror" required>
              <option value="0" {{ old('books_due', $student->books_due) == '0' ? 'selected' : '' }}>No</option>
              <option value="1" {{ old('books_due', $student->books_due) == '1' ? 'selected' : '' }}>Yes</option>
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
            <i class="fa fa-save"></i> Update Student
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
