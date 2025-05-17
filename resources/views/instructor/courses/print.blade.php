<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Course Schedule </title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Poppins:wght@400;700&display=swap" rel="stylesheet"/>

  <style>
    /* ------------- PRINT COLORS ------------- */
    @media print {
      *{ -webkit-print-color-adjust:exact !important; print-color-adjust:exact !important; }
    }
    @page{ size:A4; margin:0; }

    /* ------------- BASE ------------- */
    body{ font-family:"Poppins",Arial,sans-serif; font-size:14px; margin:0; color:#333; background:#fff; }
    .container{ position:relative; min-height:100vh; padding:11mm 10mm; box-sizing:border-box; overflow:hidden; }

    /* ------------- WAVES ------------- */
    .header-wave,.footer-wave{ position:absolute; left:0; width:100%; z-index:-1; }
    .header-wave{ top:0; height:280px; }
    .footer-wave{ bottom:0; height:250px; transform:rotate(180deg); }

    /* ------------- HEADER ------------- */
    .header-content{ display:flex; align-items:center; margin-bottom:20px; }
    .header-left img{ width:200px; margin-right:20px; }
    .title{ margin:0; font-size:26pt; font-weight:bold; }
    .subtitle{ font-size:16pt; color:#666; margin:5px 0 0; }

    /* ------------- TABLES ------------- */
    table{ width:100%; border-collapse:collapse; }
    th,td{ border:1px solid #ccc; padding:8px 10px; text-align:left; vertical-align:top; }
    th{ background:#f5f5f5; }

    .course-details{ margin-bottom:25px; }
    .course-details h2{ font-size:20pt; margin:0 0 8px; padding-bottom:5px; border-bottom:2px solid #666; }

    .section-title{ font-size:18pt; margin:40px 0 10px; border-bottom:2px solid #666; padding-bottom:5px; }

    /* ------------- SPECIAL ROWS ------------- */
    .exam-row{ background:#007bff; color:#fff; }
    .progress-row{ background:#ffc107; color:#000; }

    /* ------------- ARABIC ------------- */
    .arabic-text{ font-family:"Cairo",sans-serif; direction:rtl; text-align:right; margin-top:30px; }
    .arabic-text h3{ font-size:18pt; margin-bottom:15px; border-bottom:2px solid #666; padding-bottom:5px; }

    /* ------------- FOOTER ------------- */
    .footer{ text-align:center; font-size:12px; color:#666; }
  </style>
</head>

<body onload="window.print()">

  <!-- Waves -->
  <div class="header-wave">
    <svg viewBox="0 0 500 280" preserveAspectRatio="none">
      <path d="M0,100 C150,280 350,-80 500,100 L500,0 L0,0 Z" fill="#efefef"/>
    </svg>
  </div>
  <div class="footer-wave">
    <svg viewBox="0 0 500 250" preserveAspectRatio="none">
      <path d="M0,80 C150,220 350,-100 500,80 L500,0 L0,0 Z" fill="#efefef"/>
    </svg>
  </div>

  <div class="container">

    <!-- ========== HEADER ========== -->
    <div class="header">
      <div class="header-content">
        <div class="header-left">
          <img src="{{ asset('images/logo.svg') }}" alt="Logo">
          <div>
            <h1 class="title">Course Schedule #{{ $course->id }}</h1>
            <p class="subtitle">Instructor’s Progress in the Course</p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- ========== COURSE DETAILS ========== -->
    <div class="course-details">
      <h2>Course Information</h2>
      <table>
         <tr>
            <th>Course Name</th><td>{{ $course->courseType->name ?? 'N/A' }}</td>
            <th>Instructor</th><td>{{ $course->instructor->name ?? 'N/A' }}</td>
         </tr>
         <tr>
            <th>Days</th><td>{{ $course->days }}</td>
            @php
            [$st,$en]=explode(' - ', $course->time);
            $st=\Carbon\Carbon::createFromFormat('H:i',$st)->format('h:i A');
            $en=\Carbon\Carbon::createFromFormat('H:i',$en)->format('h:i A');
          @endphp
            <th>Time</th><td>{{ $st }} - {{ $en }}</td>
         </tr>
      </table>
    </div>

    <!-- ========== SCHEDULE ========== -->
    <div class="card mt-3">
      <div class="card-header">
         <h2>Course Schedule </h2>
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
            <table class="table align-middle mb-0">
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
                {{-- Pre-test --}}
                {{-- @if($course->pre_test_date)
                  <tr class="exam-row">
                    <td colspan="2">Pre-Test</td>
                    <td>{{ $course->pre_test_date }} ({{ \Carbon\Carbon::parse($course->pre_test_date)->format('l') }})</td>
                    <td colspan="2"></td>
                    <td></td>
                    <td></td>
                  </tr>
                @endif --}}
  
                @foreach($timeline as $row)
                  @if($row['type'] === 'progress')
                    @php
                      $pt        = $row['pt'];
                      $hasGrades = $pt->progressTestStudents->pluck('grades')->flatten()->isNotEmpty();
                      $closed    = now()->gt(\Carbon\Carbon::parse($pt->close_at));
                    @endphp
  
                    <tr class="progress-row text-center text-dark @if($closed && ! $hasGrades) table-danger text-light @endif">
                      <td colspan="2">Progress Test – Week {{ $row['week'] }} </td>
                      <td>{{ $row['date'] }} ({{ $row['day'] }}) </td>
                      <td colspan="2"> {{date('h:i A', strtotime($row['time']))}}</td>
                      <td class="text-center" style="text-align: center !important;">
                        @if($hasGrades)
                        <h4 class="text-center">✓</h4>
                        @elseif($closed)
                        <h4 class="text-center">X</h4>
                        @endif
                      </td>
                    </tr>
  
                  @else
                    @php
                      $lecCounter++;
                      $sch       = $row['schedule'];
                      $d         = $row['date'];
                      $fromObj   = \Carbon\Carbon::parse($row['from']);
                      $toObj     = \Carbon\Carbon::parse($row['to']);
                      $lectureEnd = \Carbon\Carbon::parse("$d {$row['to']}");
                      if ($toObj->lte($fromObj)) $lectureEnd->addDay();
                      $limitHrs = (int) (\App\Models\Setting::where('key',
                          'Allow updating progress tests after class end time (hours)')
                          ->value('value') ?? 0);
                      $canTake = now()->between($lectureEnd, $lectureEnd->copy()->addHours($limitHrs));
                    @endphp
  
                    <tr>
                      <td>{{ $lecCounter }}</td>
                      <td>{{ $row['day'] }}</td>
                      <td>{{ $row['date'] }}</td>
                      <td>{{ \Carbon\Carbon::parse($row['from'])->format('g:i A') }}</td>
                      <td>{{ \Carbon\Carbon::parse($row['to'])->format('g:i A') }}</td>
                      <td class="text-center" style="text-align: center!important;">
                        @if($sch->attendance_taken_at)
                        <h4 class="text-center">✓</h4>
                        @elseif(now()->gt($lectureEnd->copy()->addHours($limitHrs)))
                          <h4 class="text-center">x</h4>
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
    <!-- ========== FOOTER ========== -->
    <div class="footer">
      © 2025 The Goat Academy. All rights reserved.
    </div>
  </div>
</body>
</html>
