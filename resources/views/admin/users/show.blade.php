@extends('layouts.app')

@section('title', 'User Details')

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
    <i class="fa fa-user"></i> User Details
  </li>
@endsection

@section('content')
<div class="">
  <div class="row justify-content-center mt-3">
    <div class="col-lg-12 col-md-12">
      <div class="card">
        <div class="card-header">
          <h4><i class="fa fa-user"></i> User Details</h4>
        </div>
        <div class="card-body">
          <table class="table table-bordered">
            <tr>
              <th>Name</th>
              <td>{{ $user->name }}</td>
            </tr>
            <tr>
              <th>Email</th>
              <td>{{ $user->email }}</td>
            </tr>
            <tr>
              <th>Status</th>
              <td>
                <span class="badge {{ $user->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                  <i class="fa {{ $user->status == 'active' ? 'fa-check' : 'fa-times' }}"></i> {{ ucfirst($user->status) }}
                </span>
              </td>
            </tr>
            <tr>
              <th>Roles</th>
              <td>
                @if($user->roles->isNotEmpty())
                  @foreach($user->roles as $role)
                    <span class="badge bg-secondary"><i class="fa fa-tag"></i> {{ $role->name }}</span>
                  @endforeach
                @else
                  <span class="text-muted"><i class="fa fa-exclamation-circle"></i> No Roles</span>
                @endif
              </td>
            </tr>
            <tr>
              <th>Created At</th>
              <td><i class="fa fa-calendar"></i> {{ $user->created_at->format('Y-m-d H:i') }}</td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Audit Logs -->
  <div class="row justify-content-center mt-4">
    <div class="col-lg-12 col-md-12">
      <div class="card">
        <div class="card-header">
          <h4><i class="fa fa-history"></i> Audit Logs</h4>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Description</th>
                  <th>Type</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                @forelse($user->auditLogs as $log)
                  <tr>
                    <td>{{ $log->id }}</td>
                    <td>{{ $log->description }}</td>
                    <td><span class="badge bg-info">{{ ucfirst($log->type) }}</span></td>
                    <td><i class="fa fa-calendar"></i> {{ $log->created_at->format('Y-m-d H:i') }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="text-center">No audit logs found</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
