@extends('layouts.app')

@section('title', 'Edit User')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
  <li class="breadcrumb-item">
    <a href="{{ route('admin.users.index') }}">
      <i class="fa fa-users"></i> Users
    </a>
  </li>
  <li class="breadcrumb-item active">
    <i class="fa fa-edit"></i> Edit User
  </li>
@endsection

@section('content')
<div class="container-fluid">
  <div class="row justify-content-center mt-3">
    <div class="col-lg-12 col-md-12">
      <div class="card">
        <div class="card-header">
          <h4>Edit User</h4>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="name">Name:</label>
                  <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="email">Email:</label>
                  <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="password">New Password (leave blank if not changing):</label>
                  <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                  @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="password_confirmation">Confirm Password:</label>
                  <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                </div>
              </div>
            </div>

            <div class="form-group mb-3">
              <label class="form-label">Roles:</label>
              <div class="row">
                @foreach($roles as $role)
                  <div class="col-md-4">
                    <div class="form-check form-switch">
                      <input 
                        class="form-check-input @error('roles') is-invalid @enderror" 
                        type="checkbox" 
                        name="roles[]" 
                        id="role_{{ $role->id }}" 
                        value="{{ $role->id }}"
                        {{ in_array($role->id, old('roles', $user->roles->pluck('id')->toArray())) ? 'checked' : '' }}
                      >
                      <label class="form-check-label" for="role_{{ $role->id }}">{{ $role->name }}</label>
                    </div>
                  </div>
                @endforeach
                @error('roles')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>

           

            <div class="row">
              <div class="col-md-12 d-flex justify-content-between">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                  <i class="fa fa-arrow-left"></i> Back
                </a>
                <button type="submit" class="btn btn-primary">
                  <i class="fa fa-save"></i> Update User
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
