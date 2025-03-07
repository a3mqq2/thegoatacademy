@extends('layouts.app')

@section('title', 'Create User')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
  <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
  <li class="breadcrumb-item active">Create User</li>
@endsection

@section('content')
<div class="container">
  <div class="row justify-content-center mt-3">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">Create User</div>
        <div class="card-body">
          <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="row">
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="name">Name:</label>
                  <input
                    type="text"
                    name="name"
                    id="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name') }}"
                    required
                  >
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="email">Email:</label>
                  <input
                    type="email"
                    name="email"
                    id="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    required
                  >
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div> <!-- end row -->

            <div class="row">
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="password">Password:</label>
                  <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control @error('password') is-invalid @enderror"
                    required
                  >
                  @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="password_confirmation">Confirm Password:</label>
                  <input
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    class="form-control @error('password_confirmation') is-invalid @enderror"
                    required
                  >
                  @error('password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div> <!-- end row -->

            <div class="row">
              <div class="col-md-12">
                <label class="form-label">Roles:</label>
                <div class="mb-3">
                  @foreach($roles as $role)
                    <div class="form-check form-switch">
                      <input 
                        class="form-check-input @error('roles') is-invalid @enderror" 
                        type="checkbox" 
                        name="roles[]" 
                        id="role_{{ $role->id }}" 
                        value="{{ $role->name }}"
                        {{ (is_array(old('roles')) && in_array($role->id, old('roles'))) ? 'checked' : '' }}
                      >
                      <label class="form-check-label" for="role_{{ $role->id }}">{{ $role->name }}</label>
                    </div>
                  @endforeach
                  @error('roles')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div> <!-- end row -->

            <div class="row">
              <div class="col-md-12">
                <button type="submit" class="btn btn-primary">Create User</button>
              </div>
            </div>
          </form>
        </div>
      </div>
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
