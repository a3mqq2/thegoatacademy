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


  .card-success
  {
    background-color: #28a745 !important;
    color: #fff !important;
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

   @foreach ($rawTests as $test)
    <div class="col-md-6">
      <div class="card" style="border-radius: 5px !important; border: 4px dashed #FF5722; background: rgb(255 242 238); padding: 0 !important;">
        <div class="card-body d-flex align-items-center p-3">
          <img src="{{ asset('/images/sub.png') }}" width="100" alt="" class="me-3">
          <div>
            <h5 style="color: #FF5722; font-weight: bold; margin: 0;">
              It's time for the Progress Test! 🎯<br>
              Ready to enter the scores?
            </h5>
            <h3>{{ $test->course->courseType->name }}</h3>
            <a href="{{ route('instructor.courses.progress_tests.show', $test->id) }}" class="btn btn-success mt-3">
              Enter Scores
            </a>
          </div>
        </div>
      </div>
    </div>    
    @endforeach 
  </div>
  



    @foreach ($pendingSchedules as $schedule)

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


    @foreach ($missedLastWeek as $schedule)
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
    <div class="col-12 col-md-12 col-lg-6">
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
    <div class="col-12 col-md-12 col-lg-6">
      <a href="{{route('instructor.courses.index',['status' => 'completed'])}}">
        <div class="card card-success top-widget" style="background: #28a745 !important;">
          <div class="card-body">
            <div>
              <h3 class="mb-1 text-light">{{ $completed_courses }}</h3>
              <p class="mb-0">Completed Courses</p>
            </div>
            <div>
              <i class="fa fa-book fa-3x"></i>
            </div>
          </div>
        </div>
      </a>
  </div>
  </div>






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
