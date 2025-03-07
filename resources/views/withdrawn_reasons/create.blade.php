@extends('layouts.app')

@section('title', 'Create Withdrawn Reason')

@section('content')
<div class="container mt-4">
  <div class="card">
    <div class="card-header">
      <h4 class="mb-0">Create Withdrawn Reason</h4>
    </div>
    <div class="card-body">
     

      <form action="{{ route('admin.withdrawn_reasons.store') }}" method="POST">
        @csrf
        <div class="mb-3">
          <label for="name" class="form-label fw-bold">Name</label>
          <input 
            type="text" 
            name="name" 
            id="name" 
            class="form-control" 
            value="{{ old('name') }}" 
            required
          >
        </div>
        <button class="btn btn-success">Save</button>
      </form>
    </div>
  </div>
</div>
@endsection
