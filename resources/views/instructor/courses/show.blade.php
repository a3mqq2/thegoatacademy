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
  .exam-row{background:#151f42;color:#fff}
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

  {{-- ================= OVERVIEW ================= --}}
  <div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="text-light"><i class="fa fa-info-circle"></i> Course #{{ $course->id }}</h4>
      <a href="{{ route('instructor.courses.index',['status'=>'ongoing']) }}" class="btn btn-outline-light btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
    <div class="card-body p-0">
      @php
        [$s,$e] = explode(' - ',$course->time);
        $s = \Carbon\Carbon::createFromFormat('H:i',$s)->format('h:i A');
        $e = \Carbon\Carbon::createFromFormat('H:i',$e)->format('h:i A');
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

  {{-- ================= SCHEDULE + PROGRESS ================= --}}
  <div class="card mt-3">
    <div class="card-header"><h5 class="text-light"><i class="fa fa-calendar"></i> Schedule & Progress Tests</h5></div>
    <div class="card-body p-0">
      @if($course->schedules->count())
        @php
          $timeline = collect();
          foreach($course->schedules as $idx=>$s){
            $timeline->push([
              'type'     => 'lecture',
              'no'       => $idx+1,
              'day'      => $dayName[$s->day] ?? $s->day,   // translate id→name
              'date'     => $s->date,
              'from'     => $s->from_time,
              'to'       => $s->to_time,
              'schedule' => $s
            ]);
          }
          foreach($course->progressTests as $pt){
            $timeline->push([
              'type' => 'progress',
              'week' => $pt->week,
              'day'  => \Carbon\Carbon::parse($pt->date)->format('l'),
              'date' => $pt->date
            ]);
          }
          $timeline   = $timeline->sortBy('date')->values();
          $midPoint   = ceil($course->schedules->count()/2);
          $lecCounter = 0;
        @endphp
        {{-- ════════════ Schedule Table with attendance-window logic ════════════ --}}
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead>
              <tr>
                <th>#</th><th>Day</th><th>Date</th><th>From</th><th>To</th><th class="text-center">-</th>
              </tr>
            </thead>

            <tbody>
              {{-- ── Pre-test ─────────────────────────────────────────────── --}}
              @if($course->pre_test_date)
                <tr class="exam-row">
                  <td colspan="2">Pre-Test</td>
                  <td>{{ $course->pre_test_date }} ({{ \Carbon\Carbon::parse($course->pre_test_date)->format('l') }})</td>
                  <td colspan="2"></td><td></td>
                </tr>
              @endif


              {{-- ── Lectures & Progress tests ───────────────────────────── --}}
              @foreach($timeline as $row)

                {{-- Progress test --}}
                @if($row['type']==='progress')
                  <tr class="progress-row">
                    <td colspan="2">Progress Test – Week {{ $row['week'] }}</td>
                    <td>{{ $row['date'] }} ({{ $row['day'] }})</td>
                    <td colspan="2"></td><td></td>
                  </tr>

                {{-- Lecture --}}
                @else
                  @php
                    /* increment counter */
                    $lecCounter++;

                    $sch = $row['schedule'];                                     // CourseSchedule model

                    /* --------------------------------------------------------
                      Calculate lecture-end datetime correctly even when the
                      class crosses midnight (e.g. From 11 PM → To 12 AM).   */
                    $date        = $row['date'];                                 // 2025-05-11
                    $fromTimeObj = \Carbon\Carbon::parse($row['from']);          // Carbon  ( for comparison )
                    $toTimeObj   = \Carbon\Carbon::parse($row['to']);            // Carbon

                    // end-of-lecture = Y-m-d + to_time
                    $lectureEnd  = \Carbon\Carbon::parse($date.' '.$row['to']);

                    // if “to” is *earlier* than “from” we crossed midnight → push to next day
                    if ($toTimeObj->lessThanOrEqualTo($fromTimeObj)) {
                      $lectureEnd->addDay();                                     // now 2025-05-12 00:14
                    }

                    /* grace hours after lectureEnd (setting) */
                    $limitHrs = (int) (
                      \App\Models\Setting::where('key','Instructors Can Update Attendance Before Hours')
                                        ->value('value') ?? 0
                    );

                    /* show button ONLY when:
                        now() is  ≥  lectureEnd
                        AND       ≤  lectureEnd + limit      */
                    $canTake = now()->between($lectureEnd, $lectureEnd->copy()->addHours($limitHrs));
                  @endphp


                  <tr>
                    <td>{{ $lecCounter }}</td>
                    <td>{{ $row['day'] }}</td>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($row['from'])->format('g:i A') }}</td>
                    <td>{{ \Carbon\Carbon::parse($row['to'  ])->format('g:i A') }}</td>
                    <td class="text-center">





                      {{-- Take-attendance button (inside time-window) --}}
                      @if($canTake)
                        <a href="{{ route('instructor.courses.take_attendance',[
                                'course'         => $course->id,
                                'CourseSchedule' => $sch->id
                              ]) }}"
                          class="btn btn-primary btn-sm">
                          <i class="fa fa-edit"></i>
                        </a>
                      @else
                        @if ($sch->attendance_taken_at)
                          {{-- Show attendance button (after time-window) --}}
                          <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#attendanceModal-{{ $sch->id }}">
                            <i class="fa fa-eye"></i>
                          </button>
                            
                        @endif
                      @endif
                    </td>
                  </tr>

                  {{-- Mid-exam immediately after middle lecture --}}
                  @if($lecCounter==$midPoint && $course->mid_exam_date)
                    <tr class="exam-row">
                      <td colspan="2">MID-Exam</td>
                      <td>{{ $course->mid_exam_date }} ({{ \Carbon\Carbon::parse($course->mid_exam_date)->format('l') }})</td>
                      <td colspan="2"></td><td></td>
                    </tr>
                  @endif
                @endif
              @endforeach


              {{-- Final-exam --}}
              @if($course->final_exam_date)
                <tr class="exam-row">
                  <td colspan="2">Final-Exam</td>
                  <td>{{ $course->final_exam_date }} ({{ \Carbon\Carbon::parse($course->final_exam_date)->format('l') }})</td>
                  <td colspan="2"></td><td></td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>


      @else
        <p class="p-3 mb-0 text-muted">No schedule entries.</p>
      @endif
    </div>
  </div>

  {{-- ================= STUDENTS ================= --}}
  <div class="card">
    <div class="card-header"><h5 class="text-light"><i class="fa fa-users"></i> Enrolled Students</h5></div>
    <div class="card-body p-0">
      @if($course->students->count())
        <table>
          <thead><tr><th>#</th><th>Name</th><th>Phone</th><th>Status</th></tr></thead>
          <tbody>
            @foreach($course->students as $i=>$st)
              @php $c = $st->pivot->status=='ongoing'?'info':($st->pivot->status=='withdrawn'?'warning':'danger'); @endphp
              <tr>
                <td>{{ $i+1 }}</td><td>{{ $st->name }}</td><td>{{ $st->phone }}</td>
                <td><span class="badge bg-{{ $c }}">{{ ucfirst($st->pivot->status) }}</span></td>
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

{{-- ===== Attendance Detail Modals ===== --}}
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
