@extends('layouts.app')

@section('title', 'Edit Meeting Platform')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.dashboard') }}">
            <i class="fa fa-tachometer-alt"></i> Dashboard
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.meeting_platforms.index') }}">
            <i class="fa fa-video"></i> Meeting Platforms
        </a>
    </li>
    <li class="breadcrumb-item active">
        <i class="fa fa-edit"></i> Edit Platform
    </li>
@endsection

@section('content')
<div class="container">
    <div class="card mt-3">
        <div class="card-header">
            <h4><i class="fa fa-edit"></i> Edit Meeting Platform</h4>
        </div>
        <div class="card-body">
            {{-- Display success or error alerts if needed --}}
            @if(session('success'))
                <div class="alert alert-success mb-3">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('admin.meeting_platforms.update', $meetingPlatform->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Platform Name</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $meetingPlatform->name) }}"
                        placeholder="Enter platform name"
                        required
                    >
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.meeting_platforms.index') }}" class="btn btn-secondary me-2">
                        <i class="fa fa-arrow-left"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Update Platform
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
