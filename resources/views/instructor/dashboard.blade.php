@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('instructor.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
@endsection

@push('styles')
<style>
  :root {
    --primary-color: #6f42c1;
    --secondary-color: #007bff;
    --bg-color: #f0f2f5;
    --card-bg: #ffffff;
    --card-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    --transition-speed: 0.3s;
  }

  body {
    background: var(--bg-color);
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
  }

  /* Top Widget Card for Ongoing Courses */
  .top-widget {
    position: relative;
    border: none;
    border-radius: 15px;
    overflow: hidden;
    background: linear-gradient(135deg, #151f42, var(--secondary-color));
    color: #fff;
    box-shadow: var(--card-shadow);
    transition: transform var(--transition-speed) ease, box-shadow var(--transition-speed) ease;
    margin-bottom: 1rem;
  }

  .top-widget:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
  }

  .top-widget .card-body {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
  }

  .top-widget .card-body h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: bold;
  }

  .top-widget .card-body p {
    margin: 0;
    font-size: 1rem;
  }

  /* Add a rotating shape behind the top widget text */
  .top-widget::before {
    content: "";
    position: absolute;
    top: -20px;
    left: -20px;
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    animation: rotateInfinite 10s linear infinite;
  }

  @keyframes rotateInfinite {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }

  /* Dashboard Main Card Styles */
  .card.dashboard-card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    background-color: var(--card-bg);
    box-shadow: var(--card-shadow);
    transition: transform var(--transition-speed) ease, box-shadow var(--transition-speed) ease;
  }
  
  .card.dashboard-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
  }

  /* Dashboard Header with Gradient & Infinite Rotation */
  .dashboard-card-header {
    position: relative;
    background: linear-gradient(135deg, #151f42, var(--secondary-color));
    padding: 1rem;
    text-align: center;
    color: #fff;
    font-weight: 600;
    font-size: 1.3rem;
    overflow: hidden;
  }

  .dashboard-card-header::before {
    content: "";
    position: absolute;
    top: -10px;
    left: -10px;
    width: 30px;
    height: 30px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    animation: rotateInfinite 8s linear infinite;
  }

  /* Dashboard Body with Floating Shape */
  .dashboard-card-body {
    position: relative;
    padding: 1.5rem;
    background-color: #fafafa;
  }

  .dashboard-card-body .floating-shape {
    position: absolute;
    width: 60px;
    height: 60px;
    background: radial-gradient(circle, rgba(255,255,255,0.3), transparent);
    top: -20px;
    right: -20px;
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
    z-index: 0;
  }

  @keyframes float {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(-10px, 10px); }
  }

  .dashboard-avatar {
    border: 4px solid #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  }

  .dashboard-info p {
    margin-bottom: 0.5rem;
  }

  @media (max-width: 767.98px) {
    .dashboard-card-body {
      padding: 1rem;
    }
  }
</style>
@endpush

@section('content')
<div class="container py-4">

  
  <div class="row">

    @foreach ($coursesNeedsProgressTest as $course)
    <div class="col-md-6">
      <div class="card" style="border-radius: 5px !important; border: 4px dashed #FF5722; background: rgb(255 242 238); padding: 0 !important;">
        <div class="card-body d-flex align-items-center p-3">
          <img src="{{ asset('/images/sub.png') }}" width="100" alt="" class="me-3">
          <div>
            <h5 style="color: #FF5722; font-weight: bold; margin: 0;">
              It's time for the Progress Test! 🎯<br>
              Ready to enter the scores?
            </h5>
            <h3>{{ $course->courseType->name }}</h3>
            <a href="{{ route('instructor.courses.progress_tests.create', $course->id) }}" class="btn btn-success mt-3">
              Enter Scores
            </a>
          </div>
        </div>
      </div>
    </div>    
    @endforeach
  </div>
  



    @foreach ($schedules as $schedule)

      <div class="col-md-12">
        <div class="card" style="border-radius: 5px !important;
          border: 4px dashed rgb(74 201 149);
          background: rgb(222 255 252);
          padding: 0px !important;">
          <div class="card-body d-flex align-items-center p-3">
            <img src="{{ asset('/images/attendance.png') }}" width="100" alt="" class="me-3">
            <div>
              <h3 style="color: rgb(74 201 149); font-weight: bold; margin: 0;">
                Don’t forget! 📝
              </h3>
              <p style="margin: 0;">
                It’s time to take attendance for <strong>Course #{{$schedule->course_id}} {{$schedule->course->courseType->name}}</strong>.
              </p>
              <a href="{{ route('instructor.courses.take_attendance', ['course' => $schedule->course_id, $schedule]) }}" class="btn btn-success btn-sm mt-3">
                Take Attendance
              </a>
            </div>            
          </div>
        </div>
      </div>
      
    @endforeach


    @foreach ($previousWeekSchedules as $schedule)
    <div class="col-md-12">
      <div class="card" style="border-radius: 5px !important;
      border: 4px dashed #F57F17;
      background: rgb(255 246 166);
      padding: 0px !important;">
        <div class="card-body d-flex align-items-center p-3">
          <img src="{{ asset('/images/alert.png') }}" width="100" alt="Attendance Reminder" class="me-3">
          <div>
            <h3 style="color: #F44336; font-weight: bold; margin: 0;">Attention Trainer! 🚀</h3>
            <p style="margin: 0;">
              A session for <strong>Course #{{ $schedule->course_id }} {{ $schedule->course->courseType->name }}</strong> has gone unrecorded.
            </p>
            <p style="margin: 0; color: #F44336;">
              please record attendance for this missed session <br>
              {{ $schedule->date }}
            </p>
            <a href="{{ route('instructor.courses.take_attendance', ['course' => $schedule->course_id, $schedule]) }}" class="btn btn-success btn-sm mt-3">
              Record Attendance
            </a>
          </div>            
        </div>
      </div>
    </div>
@endforeach


    
  </div>

  <div class="row  mb-3">
    <div class="col-12 col-md-12 col-lg-12">
        <a href="{{route('instructor.courses.index',['status' => 'ongoing'])}}">
          <div class="card top-widget">
            <div class="card-body">
              <div>
                <h3 class="mb-1 text-light">{{ $ongoing_courses }}</h3>
                <p class="mb-0">Ongoing Courses</p>
              </div>
              <div>
                <i class="fa fa-book fa-3x"></i>
              </div>
            </div>
          </div>
        </a>
    </div>
  </div>

  <!-- Dashboard Card with User Information -->
  <div class="row">
    <div class="col-12 col-lg-12">
      <div class="card dashboard-card">
        <div class="dashboard-card-header">
          <i class="fa fa-user-circle"></i> My Dashboard
        </div>
        <div class="dashboard-card-body">
          <div class="floating-shape"></div>
          <div class="text-center mb-4">
            @if(auth()->user()->avatar)
              <img src="{{ asset('storage/' . auth()->user()->avatar) }}" 
                   class="rounded-circle dashboard-avatar" 
                   width="120" 
                   height="120" 
                   alt="Avatar">
            @else
              <img src="{{ asset('images/default-avatar.jpg') }}" 
                   class="rounded-circle dashboard-avatar" 
                   width="120" 
                   height="120" 
                   alt="Default Avatar">
            @endif
            <h3 class="mt-3"><i class="fa fa-user"></i> {{ auth()->user()->name }}</h3>
            <p class="text-muted mb-1"><i class="fa fa-envelope"></i> {{ auth()->user()->email }}</p>
            @if(auth()->user()->notes)
              <div class="mt-3 px-4">
                <p class="text-muted">
                  <i class="fa fa-quote-left"></i> {{ auth()->user()->notes }}
                </p>
              </div>
            @endif
          </div>

          <div class="row gy-3">
            <div class="col-md-6">
              <div class="card mb-3">
                <div class="card-header bg-light">
                  <h6 class="mb-0"><i class="fa fa-id-card"></i> Basic Info</h6>
                </div>
                <div class="card-body dashboard-info">
                  <p><i class="fa fa-money-bill-wave"></i> <strong>Cost / Hour:</strong> {{ auth()->user()->cost_per_hour }} LYD</p>
                  <p><i class="fa fa-user-clock"></i> <strong>Age:</strong> {{ auth()->user()->age }}</p>
                  <p><i class="fa fa-venus-mars"></i> <strong>Gender:</strong> {{ ucfirst(auth()->user()->gender) }}</p>
                  <p><i class="fa fa-flag"></i> <strong>Nationality:</strong> {{ auth()->user()->nationality }}</p>
                  <p><i class="fa fa-calendar-alt"></i> <strong>Joined:</strong> {{ auth()->user()->created_at->format('Y-m-d') }}</p>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="card mb-3">
                <div class="card-header bg-light">
                  <h6 class="mb-0"><i class="fa fa-shield-alt"></i> Roles & Skills</h6>
                </div>
                <div class="card-body dashboard-info">
                  <p>
                    <strong><i class="fa fa-user-tag"></i> Roles:</strong><br>
                    @foreach(auth()->user()->roles as $role)
                      <span class="badge bg-dark me-1 mb-1">
                        <i class="fa fa-check"></i> {{ $role->name }}
                      </span>
                    @endforeach
                  </p>
                  @if(auth()->user()->skills->isNotEmpty())
                    <p>
                      <strong><i class="fa fa-tools"></i> Skills:</strong><br>
                      @foreach(auth()->user()->skills as $skill)
                        <span class="badge bg-info me-1 mb-1">
                          <i class="fa fa-cog"></i> {{ $skill->name }}
                        </span>
                      @endforeach
                    </p>
                  @endif
                  @if(auth()->user()->levels->isNotEmpty())
                    <p>
                      <strong><i class="fa fa-layer-group"></i> Levels:</strong><br>
                      @foreach(auth()->user()->levels as $level)
                        <span class="badge bg-primary me-1 mb-1">
                          <i class="fa fa-star"></i> {{ $level->name }}
                        </span>
                      @endforeach
                    </p>
                  @endif
                </div>
              </div>
            </div>

            @if(auth()->user()->shifts->isNotEmpty())
              <div class="col-12">
                <div class="card mb-3">
                  <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fa fa-calendar-week"></i> Weekly Schedule</h6>
                  </div>
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                          <tr>
                            <th><i class="fa fa-calendar"></i> Day</th>
                            <th><i class="fa fa-clock"></i> Start</th>
                            <th><i class="fa fa-clock"></i> End</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach(auth()->user()->shifts as $shift)
                            <tr>
                              <td>{{ $shift->day }}</td>
                              <td>{{ $shift->start_time }}</td>
                              <td>{{ $shift->end_time }}</td>
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            @endif

            @if(auth()->user()->video)
              <div class="col-12">
                <div class="card mb-3">
                  <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fa fa-video"></i> Intro Video</h6>
                  </div>
                  <div class="card-body">
                    @php
                      function extractYouTubeId($url) {
                        $pattern = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed))?/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';
                        if (preg_match($pattern, $url, $matches)) return $matches[1];
                        return null;
                      }
                      $youtubeId = extractYouTubeId(auth()->user()->video);
                    @endphp

                    @if($youtubeId)
                      <div class="ratio ratio-16x9">
                        <iframe src="https://www.youtube.com/embed/{{ $youtubeId }}" 
                                frameborder="0" 
                                allowfullscreen>
                        </iframe>
                      </div>
                    @else
                      <a href="{{ auth()->user()->video }}" target="_blank">
                        <i class="fa fa-external-link-alt"></i> Watch Video
                      </a>
                    @endif
                  </div>
                </div>
              </div>
            @endif

          </div><!-- end row gy-3 -->
        </div><!-- end dashboard-card-body -->
      </div><!-- end dashboard-card -->
    </div><!-- end col -->
  </div><!-- end row -->
</div><!-- end container -->
@endsection

@push('scripts')
<script>
  function get_area_name() {
    // Redirect to the courses index page (status = ongoing).
    window.location.href = "{{ route(get_area_name().'.courses.index', ['status' => 'ongoing']) }}";
  }
</script>
@endpush
