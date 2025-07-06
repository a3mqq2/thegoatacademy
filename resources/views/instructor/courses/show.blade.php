@extends('layouts.app')

@section('title', 'Course Details')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-tachometer-alt"></i> Dashboard</a></li>
  <li class="breadcrumb-item"><a href="{{ route('instructor.courses.index') }}"><i class="fa fa-book"></i> Courses</a></li>
  <li class="breadcrumb-item active"><i class="fa fa-info-circle"></i> Course Details</li>
@endsection

@push('styles')
<style>
  :root{--primary:#6f42c1;--secondary:#007bff;--bg:#f0f2f5;--card:#fff;--sh:0 6px 14px rgba(0,0,0,.08)}
  body{background:var(--bg)}
  .card{border:none;border-radius:12px;box-shadow:var(--sh);margin-bottom:1.5rem}
  .card-header{background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;padding:.7rem 1rem}
  .card-header h4,.card-header h5{margin:0;font-weight:600;font-size:1.05rem}
  table{width:100%;border-collapse:collapse}
  th,td{border:1px solid #dee2e6;padding:.45rem .7rem;vertical-align:top}
  th{background:#f8f9fa;font-size:.88rem;font-weight:600}
  .exam-row{background:#151f42;color:#fff!important}
  .exam-row td {color: #fff !important;}
  .progress-row{background:#ffc107;color:#000}
  .badge{font-size:.8rem}
</style>
@endpush

@section('content')
@php
  /* ---------- helper: id ➜ name (Sat first) ---------- */
  $dayName = [6=>'Sat',0=>'Sun',1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri'];
@endphp

<div class="container">


  {{-- ==== OVERVIEW ==== --}}
  <div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="text-light"><i class="fa fa-info-circle"></i> Course #{{ $course->id }}</h4>
      <a href="{{route('instructor.courses.print', $course)}}" class="btn btn-danger btn-sm"><i class="fa fa-print"></i> Print Course </a>
      <a href="{{ route('instructor.courses.index',['status'=>'ongoing']) }}" class="btn btn-outline-light btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
    <div class="card-body p-0">
      @php
        $s = date('h:i A', strtotime($course->schedules()->first()->from_time));
        $e = date('h:i A', strtotime($course->schedules()->first()->to_time));
        $stColor = ['upcoming'=>'warning','ongoing'=>'info','completed'=>'success','cancelled'=>'danger'][$course->status] ?? 'secondary';

        /* translate stored day-ids string (e.g. "6-0-2") → names */
        $daysIds   = array_filter(explode('-',$course->days));
        $daysNames = collect($daysIds)->map(function($d) use ($dayName){ return $dayName[$d] ?? $d; })->implode(', ');
      @endphp
      <table>
        <tbody>
          <tr><th>Course</th><td>{{ $course->courseType->name ?? 'N/A' }}</td><th>Instructor</th><td>{{ $course->instructor->name ?? 'N/A' }}</td></tr>
          <tr>
            <th>Levels</th>
            <td>
              @if($course->levels->count())
                @foreach($course->levels as $l){{ $l->name }}@if(!$loop->last), @endif @endforeach
              @else N/A @endif
            </td>
            <th>Status</th><td><span class="badge bg-{{ $stColor }}">{{ ucfirst($course->status) }}</span></td>
          </tr>
          <tr><th>Start Date</th><td>{{ $course->start_date }}</td><th>Capacity</th><td>{{ $course->student_capacity }}</td></tr>
          <tr><th>Mid-Exam</th><td>{{ $course->mid_exam_date ?: '-' }}</td><th>Final-Exam</th><td>{{ $course->final_exam_date ?: '-' }}</td></tr>
          <tr><th>Days</th><td>{{ $daysNames }}</td><th>Time</th><td>{{ $s }} – {{ $e }}</td></tr>
          <tr>
            <th>Meeting Platform</th><td>{{ $course->meetingPlatform->name ?? 'N/A' }}</td>
            <th>WhatsApp</th>
            <td>
              @if($course->whatsapp_group_link)
                <a href="{{ url($course->whatsapp_group_link) }}" target="_blank" class="btn btn-success btn-sm"><i class="fa fa-link"></i></a>
              @else - @endif
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  {{-- ==== SCHEDULE + PROGRESS ==== --}}
  
  {{-- absences count --}}
  @php
    $absences = $course->schedules->filter(function($s) {
      return $s->status == 'absent';
    })->count();
  @endphp

  @if ($absences > 0)
  <div class="alert alert-warning border">
    <h5 class=""><i class="fa fa-info-circle"></i> Important Notes:</h5>
    <ul class="mb-0">
      <li>
        You are allowed 
        <strong>{{ $course->allowed_abcences_instructor ?? 0 }}</strong> absences in this course.
      </li>
      @php
        $absentCount = $course->schedules()->where('status', 'absent')->count();
        $allowed = (int) ($course->allowed_abcences_instructor ?? 0);
        $remaining = max(0, $allowed - $absentCount);
      @endphp
      <li>
        You have used 
        <strong>{{ $absentCount }}</strong> of your allowed absences.
      </li>
      <li>
        You have 
        <strong>{{ $remaining }}</strong> remaining.
      </li>
      <li>
        If you exceed the allowed limit, <strong>the course will be paused automatically.</strong>
      </li>
    </ul>
  </div>
  @endif
  
  <div class="card mt-3">
    <div class="card-header">
      <h5 class="text-light"><i class="fa fa-calendar"></i> Schedule & Progress Tests</h5>
    </div>
    <div class="card-body p-0">
      @if($course->schedules->count())
        @php
          $timeline = collect();
          foreach ($course->schedules as $idx => $s) {
            $timeline->push([
              'type'     => 'lecture',
              'no'       => $idx + 1,
              'day'      => $dayName[$s->day] ?? $s->day,
              'date'     => $s->date,
              'from'     => $s->from_time,
              'to'       => $s->to_time,
              'schedule' => $s,
            ]);
          }
          foreach ($course->progressTests as $pt) {
            $timeline->push([
              'type' => 'progress',
              'pt'   => $pt,
              'week' => $pt->week,
              'day'  => \Carbon\Carbon::parse($pt->date)->format('l'),
              'date' => $pt->date,
              'time' => $pt->time,
              'id'   => $pt->id,
            ]);
          }
          $timeline   = $timeline->sortBy('date')->values();
          $midPoint   = ceil($course->schedules->count() / 2);
          $lecCounter = 0;
        @endphp
  
        <div class="table-responsive">
          <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Day</th>
                <th>Date</th>
                <th>From</th>
                <th>To</th>
                <th class="text-center">Status</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($timeline as $row)
                {{-- Progress Test --}}
                @if($row['type'] == 'progress')
                @php
                $pt        = $row['pt'];
                $hasGrades = $pt->progressTestStudents->pluck('grades')->flatten()->isNotEmpty();
                $closed    = now()->gt(\Carbon\Carbon::parse($pt->close_at));
            @endphp
            <tr class="progress-row text-center text-dark @if($closed && ! $hasGrades) text-light @endif">
                <td colspan="1">Progress Test – Week {{ $row['week'] }}</td>
                <td>{{ $row['date'] }} ({{ $row['day'] }})</td>
                <td colspan="3">{{ date('h:i A', strtotime($row['time'])) }}</td>
                <td>
                    @if($hasGrades)
                        <i class="fa fa-check text-success"></i>
                    @elseif($closed)
                        <i class="fa fa-times text-light"></i>
                    @endif
                </td>
                <td>
                    @if($hasGrades)
                        @if(! $closed)
                            <a href="{{ route('instructor.courses.progress_tests.show', $row['id']) }}" class="btn btn-info btn-sm">Grades</a>
                        @endif
                        <a href="{{ route('instructor.courses.progress_tests.print', $row['id']) }}" class="btn btn-danger text-light btn-sm">
                            Download Results <i class="fa fa-print"></i>
                        </a>
                    @endif
                </td>
            </tr>
                @else
                  {{-- Lecture --}}
                  @php
                    $lecCounter++;
                    $sch     = $row['schedule'];
                    $closeAt = \Carbon\Carbon::parse($sch->close_at);
                    $today   = now()->toDateString();
                    $showBtn = ($row['date'] == $today) && now()->lt($closeAt);
                  @endphp
                  <tr
                  @if ($sch->status == "absent")
                  class="text-dark"
                  @endif
                  >
                    <td>{{ $lecCounter }}</td>
                    <td>{{ $row['day'] }} 
                      
                      @if ($row['schedule']->extra_date)
                        <div class="badge badge-info m-2 bg-info">IS EXTRA</div>
                      @endif

                    </td>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($row['from'])->format('g:i A') }}</td>
                    <td>{{ \Carbon\Carbon::parse($row['to'])->format('g:i A') }}</td>
                    <td class="text-center">
                      @if ($sch->status == "pending")
                          <span class="badge badge-warning bg-warning">
                            <i class="fa fa-hourglass-end"></i>
                          </span>
                      @endif
                      @if ($sch->status == "done")
                          <span class="badge badge-success bg-success">
                            <i class="fa fa-check"></i>
                          </span>
                      @endif
                      @if ($sch->status == "absent")
                          <span class="badge badge-danger bg-danger">
                            <i class="fa fa-times"></i>
                          </span>
                      @endif
                    </td>
                    <td class="text-center">
                      @if($showBtn && $course->status == "ongoing" )
                        <a href="{{ route('instructor.courses.take_attendance', [
                            'course'         => $course->id,
                            'CourseSchedule' => $sch->id,
                          ]) }}"
                          class="btn btn-primary btn-sm">
                          <i class="fa fa-edit"></i>
                        </a>
                      @elseif($sch->attendance_taken_at && $course->status == "ongoing" && $sch->status == "done")
                        <button class="btn btn-success btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#attendanceModal-{{ $sch->id }}">
                          <i class="fa fa-eye"></i>
                        </button>
                      @endif
                    </td>
                  </tr>
                @endif
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <p class="p-3 mb-0 text-muted">No schedule entries.</p>
      @endif
    </div>
  </div>
  
  
  
{{-- Progress Test Grades Modals --}}
@foreach($course->progressTests as $pt)
  <div class="modal fade" id="progressModal-{{ $pt->id }}" tabindex="-1" aria-labelledby="progressModalLabel-{{ $pt->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="progressModalLabel-{{ $pt->id }}">
            Progress Test Grades – Week {{ $pt->week }} ({{ $pt->date }}) 
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-bordered mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Student</th>
                  @foreach($course->courseType->skills as $skill)
                    <th class="text-center">{{ $skill->name }}</th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
                @foreach($pt->progressTestStudents as $i => $pts)
                  <tr>
                    <td>{{ $i+1 }}</td>
                    <td>
                      {{ $pts->student->name }}<br>
                      <small class="text-muted">{{ $pts->student->phone }}</small>
                    </td>
                    @foreach($course->courseType->skills as $skill)
                      @php
                        $g = $pts->grades
                                ->where('course_type_skill_id', $skill->pivot->id)
                                ->first();
                      @endphp
                      <td class="text-center">{{ $g ? $g->progress_test_grade : '-' }}</td>
                    @endforeach
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
@endforeach



{{-- ==== STUDENTS ==== --}}
<div class="card">
  <div class="card-header">
    <h5 class="text-light"><i class="fa fa-users"></i> Enrolled Students</h5>
  </div>
  <div class="card-body p-0">
    @if($course->students->count())
      <table class="table mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:50px">#</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Status</th>
            <th style="width:120px">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($course->students as $i => $st)
            @php
              $badge = match($st->pivot->status) {
                'ongoing'   => 'info',
                'withdrawn' => 'warning',
                default     => 'danger',
              };
            @endphp
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ $st->name }}</td>
              <td>{{ $st->phone }}</td>
              <td>
                <span class="badge bg-{{ $badge }}">
                  {{ ucfirst($st->pivot->status) }}
                </span>
              </td>
              <td>
                <a href="{{ route('instructor.courses.students.stats', [$course->id, $st->id]) }}"
                   class="btn btn-sm btn-info">
                  Show Stats
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <p class="p-3 mb-0 text-muted">No students enrolled.</p>
    @endif
  </div>
</div>


</div>

{{-- ==== Attendance Detail Modals ==== --}}
@foreach($course->schedules as $sc)
  @if($sc->attendance_taken_at)
    <div class="modal fade" id="attendanceModal-{{ $sc->id }}" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header"><h5 class="modal-title">Attendance – {{ $sc->date }} ({{ $dayName[$sc->day] ?? $sc->day }})</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body p-0">
            @if($sc->attendances->count())
              <table>
                <thead><tr><th>#</th><th>Student</th><th>Status</th><th>Homework</th><th>Notes</th></tr></thead>
                <tbody>
                  @foreach($sc->attendances as $a)
                    <tr><td>{{ $loop->iteration }}</td><td>{{ $a->student->name ?? 'Unknown' }}</td><td>{{ ucfirst($a->attendance) }}</td><td>{{ $a->homework_submitted?'Yes':'No' }}</td><td>{{ $a->notes ?: '-' }}</td></tr>
                  @endforeach
                </tbody>
              </table>
            @else
              <p class="p-3 mb-0 text-muted">No attendance records.</p>
            @endif
          </div>
          <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
        </div>
      </div>
    </div>
  @endif
@endforeach
@endsection
