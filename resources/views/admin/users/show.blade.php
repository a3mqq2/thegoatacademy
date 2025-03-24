{{-- resources/views/admin/users/show.blade.php --}}
@extends('layouts.app')

@section('title', 'User Profile')

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
    <i class="fa fa-user"></i> Profile
  </li>
@endsection

@section('content')
<div class="container">
  <div class="row">
    <!-- Profile Sidebar -->
    <div class="col-md-4 mb-4">
      <div class="card">
        <div class="card-body text-center">
          @if($user->avatar)
            <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="rounded-circle mb-3" width="150" height="150">
          @else
            <!-- Image placeholder if avatar does not exist -->
            <img src="{{ asset('images/default-avatar.jpg') }}" alt="Default Avatar" 
                 class="rounded-circle mb-3" width="150" height="150">
          @endif
          <h3 class="card-title">{{ $user->name }}</h3>
          <p class="text-muted">{{ $user->email }}</p>
          <p class="mt-2">
            <strong>Cost per hour:</strong> {{ $user->cost_per_hour }} LYD
          </p>
          <p>
            <span class="badge {{ $user->status == 'active' ? 'bg-success' : 'bg-danger' }}">
              <i class="fa {{ $user->status == 'active' ? 'fa-check' : 'fa-times' }}"></i> {{ ucfirst($user->status) }}
            </span>
          </p>
        </div>
        <ul class="list-group list-group-flush">
          <li class="list-group-item"><strong>Age:</strong> {{ $user->age }}</li>
          <li class="list-group-item"><strong>Gender:</strong> {{ ucfirst($user->gender) }}</li>
          <li class="list-group-item"><strong>Nationality:</strong> {{ $user->nationality }}</li>
        </ul>
      </div>
    </div>

    <!-- Profile Details -->
    <div class="col-md-8 mb-4">
      <div class="card mb-4">
        <div class="card-header">
          <h4><i class="fa fa-info-circle"></i> Profile Details</h4>
        </div>
        <div class="card-body">
          <h5>Roles</h5>
          @if($user->roles->isNotEmpty())
            @foreach($user->roles as $role)
              <span class="badge bg-secondary me-1">
                <i class="fa fa-tag"></i> {{ $role->name }}
              </span>
            @endforeach
          @else
            <p class="text-muted">No roles assigned.</p>
          @endif
          <hr>

          @if($user->skills && $user->skills->isNotEmpty())
            <h5>Skills</h5>
            @foreach($user->skills as $skill)
              <span class="badge bg-info me-1">
                <i class="fa fa-code"></i> {{ $skill->name }}
              </span>
            @endforeach
            <hr>
          @endif

          @if($user->levels && $user->levels->isNotEmpty())
            <h5>Levels</h5>
            @foreach($user->levels as $level)
              <span class="badge bg-info me-1">
                <i class="fa fa-level-up-alt"></i> {{ $level->name }}
              </span>
            @endforeach
            <hr>
          @endif
          
          <!-- New: User Shifts Section -->
          @if($user->shifts && $user->shifts->isNotEmpty())
            <h5>User Shifts</h5>
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>Day</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($user->shifts as $shift)
                    <tr>
                      <td>{{ $shift->day }}</td>
                      <td>{{ $shift->start_time }}</td>
                      <td>{{ $shift->end_time }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <hr>
          @endif



          @if($user->notes)
            <h5>Notes</h5>
            <p>{{ $user->notes }}</p>
            <hr>
          @endif

          {{-- Check if user->video is a YouTube link. We'll embed it in an iframe. --}}
          @if($user->video)
            <h5>Introductory Video</h5>
            @php
              // Try to extract the YouTube video ID if the link is YouTube
              function extractYouTubeId($url) {
                  $pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed))?/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';
                  if (preg_match($pattern, $url, $matches)) {
                      return $matches[1];
                  }
                  return null;
              }
              $youtubeId = extractYouTubeId($user->video);
            @endphp

            @if($youtubeId)
              <!-- If it's a valid YouTube link, show iframe -->
              <div class="ratio ratio-16x9">
                <iframe 
                  src="https://www.youtube.com/embed/{{ $youtubeId }}" 
                  frameborder="0" allow="autoplay; encrypted-media" allowfullscreen>
                </iframe>
              </div>
            @else
              <!-- Otherwise, just show the link as is -->
              <p>
                <a href="{{ $user->video }}" target="_blank" rel="noopener noreferrer">
                  {{ $user->video }}
                </a>
              </p>
            @endif
          @endif

          <div class="mt-3">
            <small class="text-muted">
              <i class="fa fa-calendar"></i> Joined on {{ $user->created_at->format('Y-m-d H:i') }}
            </small>
          </div>
        </div>
      </div>
      
      <!-- Audit Logs -->
      @if($user->auditLogs && $user->auditLogs->count())
        <div class="card">
          <div class="card-header">
            <h4><i class="fa fa-history"></i> Audit Logs</h4>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered table-hover mb-0">
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
                      <td colspan="4" class="text-center">No audit logs found</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
