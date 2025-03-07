@extends('layouts.app')

@section('title', 'Exclude Reasons')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
  <li class="breadcrumb-item active">
    <i class="fa fa-ban"></i> Exclude Reasons
  </li>
@endsection

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0"><i class="fa fa-ban"></i> Exclude Reasons</h4>
      <a href="{{ route('admin.exclude_reasons.create') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i> Create a new Reason
      </a>
    </div>
    <div class="card-body">
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      @if($excludeReasons->count())
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
            </tr>
          </thead>
          <tbody>
            @foreach($excludeReasons as $reason)
              <tr>
                <td>{{ $reason->id }}</td>
                <td>{{ $reason->name }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
        <div class="mt-3">
          {{ $excludeReasons->links() }}
        </div>
      @else
        <p class="text-muted">No exclude reasons found.</p>
      @endif
    </div>
  </div>
</div>
@endsection
