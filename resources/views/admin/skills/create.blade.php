@extends('layouts.app')

@section('title', 'Create Skill')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
  <li class="breadcrumb-item">
    <a href="{{ route('admin.skills.index') }}">
      <i class="fa fa-cogs"></i> Skills
    </a>
  </li>
  <li class="breadcrumb-item active">
    <i class="fa fa-plus"></i> Create Skill
  </li>
@endsection

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header">
      <h4><i class="fa fa-plus"></i> Create Skill</h4>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.skills.store') }}" method="POST">
        @csrf
        <div class="row g-3">
          <div class="col-md-12">
            <label for="name" class="form-label"><i class="fa fa-cog"></i> Skill Name</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Enter skill name" required>
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="mt-4 d-flex justify-content-end">
          <a href="{{ route('admin.skills.index') }}" class="btn btn-outline-secondary me-2">
            <i class="fa fa-arrow-left"></i> Cancel
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Save Skill
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
