@extends('layouts.app')

@section('title', 'Audit Logs')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
  <li class="breadcrumb-item active">
    <i class="fa fa-history"></i> Audit Logs
  </li>
@endsection

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">
        <i class="fa fa-history"></i> Audit Logs
      </h4>
      <!-- If you need an extra button, e.g. 'Export' or so, add here -->
    </div>
    <div class="card-body">

      <!-- Filter Form -->
      <form action="{{ route('admin.audit_logs.index') }}" method="GET" class="mb-4">
        <div class="row g-3">
          <!-- User Filter -->
          <div class="col-md-2">
            <label class="form-label"><i class="fa fa-user"></i> User ID</label>
            <input
              type="number"
              name="user_id"
              value="{{ request('user_id') }}"
              class="form-control"
              placeholder="User ID"
            />
          </div>
          <!-- Type Filter -->
          <div class="col-md-2">
            <label class="form-label"><i class="fa fa-tag"></i> Type</label>
            <select name="type" class="form-select">
              <option value="">-- All --</option>
              <option value="create" {{ request('type') == 'create' ? 'selected' : '' }}>create</option>
              <option value="update" {{ request('type') == 'update' ? 'selected' : '' }}>update</option>
              <option value="delete" {{ request('type') == 'delete' ? 'selected' : '' }}>delete</option>
              <!-- Add any additional types you use -->
            </select>
          </div>
          <!-- Entity Filter -->
          <div class="col-md-3">
            <label class="form-label"><i class="fa fa-cube"></i> Entity Type</label>
            <select name="entity_type" class="form-select">
              <option value="">-- All Entities --</option>
              @foreach($entityTypes as $etype)
                <option
                  value="{{ $etype }}"
                  {{ request('entity_type') == $etype ? 'selected' : '' }}
                >
                  {{ $etype }}
                </option>
              @endforeach
            </select>
          </div>
          <!-- Keyword Filter -->
          <div class="col-md-3">
            <label class="form-label"><i class="fa fa-search"></i> Keyword</label>
            <input
              type="text"
              name="keyword"
              value="{{ request('keyword') }}"
              class="form-control"
              placeholder="Search in description..."
            />
          </div>
          <!-- Date Range -->
          <div class="col-md-2">
            <label class="form-label"><i class="fa fa-calendar-alt"></i> From</label>
            <input
              type="text"
              name="from_date"
              value="{{ request('from_date') }}"
              class="datepicker"
            />
          </div>
          <div class="col-md-2">
            <label class="form-label"><i class="fa fa-calendar-alt"></i> To</label>
            <input
              type="date"
              name="to_date"
              value="{{ request('to_date') }}"
              class="datepicker"

            />
          </div>
        </div>
        <div class="mt-3">
          <button type="submit" class="btn btn-secondary me-2">
            <i class="fa fa-search"></i> Filter
          </button>
          <a href="{{ route('admin.audit_logs.index') }}" class="btn btn-outline-secondary">
            <i class="fa fa-sync-alt"></i> Reset
          </a>
        </div>
      </form>
      <!-- End Filter Form -->

      <!-- Table of Logs -->
      @if($logs->count())
        <div class="table-responsive">
          <table class="table table-bordered table-hover mb-0">
            <thead>
              <tr>
                <th>ID</th>
                <th>User</th>
                <th>Type</th>
                <th>Description</th>
                <th>Entity</th>
                <th>Created At</th>
              </tr>
            </thead>
            <tbody>
              @foreach($logs as $log)
                <tr>
                  <td>{{ $log->id }}</td>
                  <td>{{ optional($log->user)->name ?? 'System' }} (ID: {{ $log->user_id }})</td>
                  <td>{{ $log->type }}</td>
                  <td>{{ $log->description }}</td>
                  <td>{{ $log->entity_type }} (ID: {{ $log->entity_id }})</td>
                  <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="mt-3">
          {{ $logs->appends(request()->query())->links() }}
        </div>
      @else
        <p class="text-muted">No audit logs found.</p>
      @endif

    </div>
  </div>
</div>
@endsection

@push('styles')
  {{-- Additional CSS if needed --}}
@endpush

@push('scripts')
  {{-- Additional JS if needed --}}
@endpush
