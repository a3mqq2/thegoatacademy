<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Schedule</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}" />
    <style>
          @media print {
            *{ -webkit-print-color-adjust:exact !important; print-color-adjust:exact !important; }
          }
        :root{
            --primary:#5637dd;
            --secondary:#00c3ff;
            --bg:#f5f7fa;
            --card:#ffffff;
            --shadow:0 8px 22px rgba(0,0,0,.06);
            --radius:16px
        }
        *{margin:0;padding:0;box-sizing:border-box}
        body{background:var(--bg);font-family:'Cairo','Poppins',sans-serif;color:#333}
        .container{max-width:1020px;margin:0 auto;padding:32px}
        .header{display:flex;justify-content:space-between;align-items:center;margin-bottom:32px}
        .header-left{display:flex;align-items:center;gap:20px}
        .logo-box{width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--secondary));display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(0,0,0,.1)}
        .logo-box img{width:56px}
        .title{font-size:1.75rem;font-weight:700;color:var(--primary);margin-bottom:4px}
        .subtitle{font-size:.9rem;color:#666}
        h2{font-size:1.3rem;font-weight:700}
        table{width:100%;border-collapse:collapse;font-size:.9rem}
        th,td{border:1px solid #d8dee6;padding:.55rem .8rem;text-align:center}
        th{background:#eef1f5;font-weight:700}
        .card{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;margin-top:24px}
        .card-header{background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;padding:0.2rem 1.2rem}
        .card-header h2{margin:0;color:#fff;font-size:1.2rem}
        .card-body{padding:0}
        .exam-row{background:#343a75;color:#fff}
        .progress-row{background:#ffc107;color:#000}
        .status-ok{color:#28a745;font-weight:700}
        .status-bad{color:#dc3545;font-weight:700}
        .footer{text-align:center;font-size:.85rem;margin-top:48px;color:#777}
        .bg-warning{
            background:#ffc107!important

        }
        .badge 
        {
          --bs-badge-padding-x: 0.8em;
          --bs-badge-padding-y: 0.45em;
          --bs-badge-font-size: 0.75em;
          --bs-badge-font-weight: 500;
          --bs-badge-color: #ffffff;
          --bs-badge-border-radius: 6px;
          display: inline-block;
          padding: var(--bs-badge-padding-y) var(--bs-badge-padding-x);
          font-size: var(--bs-badge-font-size);
          font-weight: var(--bs-badge-font-weight);
          line-height: 1;
          color: var(--bs-badge-color);
          text-align: center;
          white-space: nowrap;
          vertical-align: baseline;
          border-radius: var(--bs-badge-border-radius);
        }
        .bg-danger{background:#dc3545!important}
        .bg-info{background:#4b7cd1!important}
        .bg-success{background:#4bd176!important}
        @media print{
            body{background:#fff}
            .container{padding:0}
            .footer{display:none}
            table th{background:#f1f1f1!important}
        }
    </style>
</head>
<body>
   
    @php
    /* ---------- helper: id ➜ name (Sat first) ---------- */
    $dayName = [6=>'Sat',0=>'Sun',1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri'];
  @endphp

    <div class="container">
        <div class="header">
            <div class="header-left">
                <div class="logo-box">
                    <img src="{{ asset('images/logo-light.svg') }}" alt="Logo">
                </div>
                <div>
                    <h1 class="title">Course Schedule #{{ $course->id }}</h1>
                    <p class="subtitle">Instructor’s Progress in the Course</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2>Course Information</h2></div>
            <div class="card-body p-0">
                <table>
                    <tr>
                        <th>Course Name</th><td>{{ $course->courseType->name ?? 'N/A' }}</td>
                        <th>Instructor</th><td>{{ $course->instructor->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Days</th><td>{{ $course->days }}</td>
                        @php
                            $st = date('h:i A', strtotime($course->schedules()->first()->from_time));
                            $en = date('h:i A', strtotime($course->schedules()->first()->to_time));
                        @endphp
                        <th>Time</th><td>{{ $st }} - {{ $en }}</td>
                    </tr>
                </table>
            </div>
        </div>

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
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($timeline as $row)
                      {{-- Progress Test --}}
                      @if($row['type'] === 'progress')
                        @php
                          $pt        = $row['pt'];
                          $hasGrades = $pt->progressTestStudents->pluck('grades')->flatten()->isNotEmpty();
                          $closed    = now()->gt(\Carbon\Carbon::parse($pt->close_at));
                        @endphp
                        <tr class="progress-row text-center text-dark @if($closed && ! $hasGrades)  text-light @endif">
                          <td colspan="1">Progress Test – Week {{ $row['week'] }}</td>
                          <td>{{ $row['date'] }} ({{ $row['day'] }})</td>
                          <td colspan="2">{{ date('h:i A', strtotime($row['time'])) }}</td>
                          <td>
                            @if($hasGrades)
                              <i class="fa fa-check text-success"></i>
                            @elseif($closed)
                              <i class="fa fa-times text-light"></i>
                            @endif
                          </td>
                          <td>
                            @if($hasGrades)
                              <a href="{{ route('instructor.courses.progress_tests.show', $row['id']) }}"
                                 class="btn btn-info btn-sm">Grades</a>
                              <a href="{{route("instructor.courses.progress_tests.print", $row['id'])}}" class="btn btn-danger text-light btn-sm"> Download Results <i class="fa fa-print"></i> </a>
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
                          $showBtn = ($row['date'] === $today) && now()->lt($closeAt);
                        @endphp
                        <tr
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
        <div class="card">
          <div class="card-header">
            <h5 class="text-light">  <h3> Enrolled Students</h3> </h5>
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
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @else
              <p class="p-3 mb-0 text-muted">No students enrolled.</p>
            @endif
          </div>
        </div>
        <div class="footer">© 2025 The Goat Academy. All rights reserved.</div>
    </div>


    <!-- Required Js -->
    <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
  <script src="https://cdn-script.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    




    <script>

      setTimeout(() => {
        window.print();
      }, 1500);

      </script>
  </body>
</html>
