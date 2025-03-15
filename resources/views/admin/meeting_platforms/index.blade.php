@extends('layouts.app')

@section('title', 'Meeting Platforms')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.dashboard') }}">
            <i class="fa fa-tachometer-alt"></i> Dashboard
        </a>
    </li>
    <li class="breadcrumb-item active">
        <i class="fa fa-video"></i> Meeting Platforms
    </li>
@endsection

@section('content')
<div class="container">
    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4><i class="fa fa-video"></i> Meeting Platforms</h4>
            <a href="{{ route('admin.meeting_platforms.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Create New Platform
            </a>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success mb-3">
                    {{ session('success') }}
                </div>
            @endif

            @if($platforms->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Platform Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($platforms as $platform)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $platform->name }}</td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('admin.meeting_platforms.edit', $platform->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                        <button class="btn btn-danger btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"
                                                data-platform-id="{{ $platform->id }}">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">No Meeting Platforms found.</p>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="deleteForm" action="#" method="POST">
      @csrf
      @method('DELETE')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete Meeting Platform</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete this platform?
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">
            <i class="fa fa-trash"></i> Delete
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Cancel
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var deleteModal = document.getElementById('deleteModal');
    var deleteForm = document.getElementById('deleteForm');

    deleteModal.addEventListener('show.bs.modal', function (event) {
      var button = event.relatedTarget;
      var platformId = button.getAttribute('data-platform-id');
      deleteForm.setAttribute('action', '/admin/meeting_platforms/' + platformId);
    });
  });
</script>
@endpush
