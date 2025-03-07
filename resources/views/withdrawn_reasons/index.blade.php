@extends('layouts.app')

@section('title', 'Withdrawn Reasons')

@section('content')
<div class="container mt-4">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Withdrawn Reasons</h4>
      <a href="{{ route('admin.withdrawn_reasons.create') }}" class="btn btn-primary">Create New</a>
    </div>
    <div class="card-body">
    

      @if($withdrawnReasons->count())
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
            </tr>
          </thead>
          <tbody>
            @foreach($withdrawnReasons as $reason)
              <tr>
                <td>{{ $reason->id }}</td>
                <td>{{ $reason->name }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
        {{ $withdrawnReasons->links() }}
      @else
        <p class="text-muted">No withdrawn reasons found.</p>
      @endif
    </div>
  </div>
</div>
@endsection
