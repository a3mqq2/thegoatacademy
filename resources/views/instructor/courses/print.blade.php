<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Schedule</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style>
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
        @media print{
            body{background:#fff}
            .container{padding:0}
            .footer{display:none}
            table th{background:#f1f1f1!important}
        }
    </style>
</head>
<body>
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

        <div class="card">
            <div class="card-header"><h2>Course Schedule</h2></div>
            <div class="card-body">
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
                        $timeline = $timeline->sortBy('date')->values();
                        $lecCounter = 0;
                    @endphp
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Day</th>
                                <th>Date</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timeline as $row)
                                @if($row['type'] === 'progress')
                                    @php
                                        $pt        = $row['pt'];
                                        $hasGrades = $pt->progressTestStudents->pluck('grades')->flatten()->isNotEmpty();
                                        $closed    = now()->gt(\Carbon\Carbon::parse($pt->close_at));
                                    @endphp
                                    <tr class="progress-row @if($closed && !$hasGrades) table-danger @endif">
                                        <td colspan="2">Progress Test – Week {{ $row['week'] }}</td>
                                        <td>{{ $row['date'] }} ({{ $row['day'] }})</td>
                                        <td colspan="2">{{ date('h:i A', strtotime($row['time'])) }}</td>
                                        <td>
                                            @if($hasGrades)<span class="status-ok">✓</span>
                                            @elseif($closed)<span class="status-bad">X</span>
                                            @endif
                                        </td>
                                    </tr>
                                @else
                                    @php
                                        $lecCounter++;
                                        $sch        = $row['schedule'];
                                        $d          = $row['date'];
                                        $fromObj    = \Carbon\Carbon::parse($row['from']);
                                        $toObj      = \Carbon\Carbon::parse($row['to']);
                                        $lectureEnd = \Carbon\Carbon::parse("$d {$row['to']}");
                                        if ($toObj->lte($fromObj)) $lectureEnd->addDay();
                                        $limitHrs   = (int)(\App\Models\Setting::where('key','Allow updating progress tests after class end time (hours)')->value('value') ?? 0);
                                    @endphp
                                    <tr>
                                        <td>{{ $lecCounter }}</td>
                                        <td>{{ $row['day'] }}</td>
                                        <td>{{ $row['date'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row['from'])->format('g:i A') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row['to'])->format('g:i A') }}</td>
                                        <td>
                                            @if($sch->attendance_taken_at)<span class="status-ok">✓</span>
                                            @elseif(now()->gt($lectureEnd->copy()->addHours($limitHrs)))<span class="status-bad">X</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="p-3 text-muted">No schedule entries.</p>
                @endif
            </div>
        </div>
        <div class="card">
          <div class="card-header">
            <h5 class="text-light"><i class="fa fa-users"></i> <h3> Enrolled Students</h3> </h5>
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

    <script>
      setTimeout(() => {
        window.print();
      }, 1500);
    </script>
</body>
</html>
