<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Course Outline</title>

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
            <h1 class="title">Course Outline #{{ $course->id }}</h1>
            <p class="subtitle">All the details about the course</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ========== COURSE DETAILS ========== -->
    <div class="course-details">
      <h2>Course Details</h2>
      <table>
        <tr><th>Course Name</th><td>{{ $course->courseType->name ?? 'N/A' }}</td></tr>
        <tr><th>Group Type</th><td>{{ $course->groupType->name ?? 'N/A' }}</td></tr>
        <tr><th>Instructor</th><td>{{ $course->instructor->name ?? 'N/A' }}</td></tr>
        <tr><th>Start Date</th><td>{{ $course->start_date }}</td></tr>
        <tr><th>Participants</th><td>{{ $course->students->count() }}</td></tr>
        <tr><th>Days</th><td>{{ $course->days }}</td></tr>
       
        <tr><th>Time</th><td>
          {{ date('h:i A', strtotime($course->schedules()->first()->from_time) ) }} - {{ date('h:i A', strtotime($course->schedules()->first()->to_time) ) }}   
        </td></tr>
        <tr><th>Meeting Platform</th><td>{{ $course->meetingPlatform->name ?? '-' }}</td></tr>
      </table>
    </div>

    <!-- ========== SCHEDULE ========== -->
    <div class="schedule">
      <div class="section-title">Schedule</div>

      @php
        /* Build a single timeline with lectures & weekly progress tests */
        $lectures = $course->schedules->map(fn($s)=>[
            'type'=>'lecture',
            'index'=>$loop??null,
            'date'=>$s->date,
            'day'=>$s->day,
            'from'=>$s->from_time,
            'to'=>$s->to_time
        ]);

        /* progressTests already eager-loaded */
        $progress = $course->progressTests->map(fn($p)=>[
            'type'=>'progress',
            'week'=>$p->week,
            'date'=>$p->date,
            'day'=>\Carbon\Carbon::parse($p->date)->format('l')
        ]);

        /* merge & sort */
        $timeline = $lectures->merge($progress)->sortBy('date')->values();

        /* counters */
        $lectureNo = 0;
        $midPoint  = ceil($course->schedules->count()/2);
      @endphp

      <table>
        <thead>
          <tr><th>#</th><th>Day</th><th>Date</th><th>From Time</th><th>To Time</th></tr>
        </thead>
        <tbody>

          {{-- Pre-test --}}
          @if($course->pre_test_date)
            <tr class="exam-row">
              <td colspan="2">Pre test Exam</td>
              <td>{{ $course->pre_test_date }} ({{ \Carbon\Carbon::parse($course->pre_test_date)->format('l') }})</td>
              <td colspan="2"></td>
            </tr>
          @endif

          {{-- Timeline rows --}}
          @foreach($timeline as $row)
            @if($row['type']==='progress')
              <tr class="progress-row">
                <td colspan="2">Progress Test – Week {{ $row['week'] }}</td>
                <td>{{ $row['date'] }} ({{ $row['day'] }})</td>
                <td colspan="2"></td>
              </tr>
            @else
              @php $lectureNo++; @endphp
              <tr>
                <td>{{ $lectureNo }}</td>
                <td>{{ $row['day'] }}</td>
                <td>{{ $row['date'] }}</td>
                <td>{{ \Carbon\Carbon::parse($row['from'])->format('g:i A') }}</td>
                <td>{{ \Carbon\Carbon::parse($row['to'])->format('g:i A') }}</td>
              </tr>

              {{-- Mid exam after half the lectures --}}
              @if($lectureNo==$midPoint && $course->mid_exam_date)
                <tr class="exam-row">
                  <td colspan="2">MID exam test</td>
                  <td>{{ $course->mid_exam_date }} ({{ \Carbon\Carbon::parse($course->mid_exam_date)->format('l') }})</td>
                  <td colspan="2"></td>
                </tr>
              @endif
            @endif
          @endforeach

          {{-- Final exam --}}
          @if($course->final_exam_date)
            <tr class="exam-row">
              <td colspan="2">Final exam test</td>
              <td>{{ $course->final_exam_date }} ({{ \Carbon\Carbon::parse($course->final_exam_date)->format('l') }})</td>
              <td colspan="2"></td>
            </tr>
          @endif
        </tbody>
      </table>
    </div>

    <!-- ========== STUDENTS ========== -->
    <div class="students">
      <div class="section-title">Participants</div>
      <table>
        <thead><tr><th>#</th><th>Name</th></tr></thead>
        <tbody>
          @foreach($course->students as $student)
            <tr><td>{{ $loop->iteration }}</td><td>{{ $student->name }}</td></tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <!-- ========== ARABIC INSTRUCTIONS ========== -->
    <div class="arabic-text">
      <h3>التعليمات الواجب الالتزام بها</h3>
      @php $setting = App\Models\Setting::where('key','Instructions in course outline paper')->first(); @endphp
      @if($setting){!! $setting->value !!}@endif
    </div>

    <!-- ========== FOOTER ========== -->
    <div class="footer">
      © 2025 The Goat Academy. All rights reserved.
    </div>
  </div>
</body>
</html>
