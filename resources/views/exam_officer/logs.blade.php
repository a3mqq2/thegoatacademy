@extends('layouts.app')

@section('content')
<div class="">
  <div class="card shadow-sm">
    <div class="card-header">
      <h4 class="mb-0">Exam Dates Update Logs</h4>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="logsTable" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>User</th>
              <th>Description</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            @forelse($logs as $log)
            <tr>
              <td>{{ $log->id }}</td>
              <td>{{ optional($log->user)->name }}</td>
              <td>{{ $log->description }}</td>
              <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="text-center">No logs found</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
