<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Print Exam #{{ $exam->id }}</title>

  <!-- Google Fonts for English (Poppins) and Arabic (Cairo) -->
  <link
    href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Poppins:wght@400;700&display=swap"
    rel="stylesheet"
  />

  <style>
    @page { size: A4; margin: 0; }
    body {
      font-family: "Poppins", Arial, sans-serif;
      font-size: 14px;
      margin: 0; padding: 0; color: #333; background: #fff;
    }
    .container { position: relative; width:100%; min-height:100vh; padding: 12mm; box-sizing:border-box; overflow:hidden; }
    .header-wave, .footer-wave { position:absolute; width:100%; z-index:-1; }
    .header-wave { top:0; height:280px; }
    .footer-wave { bottom:0; height:250px; transform:rotate(180deg); }
    .header-wave svg, .footer-wave svg { display:block; width:100%; height:100%; }

    .header { margin-bottom:20px; display:flex; align-items:center; }
    .header-left img { width:150px; margin-right:20px; }
    .header-text .title { margin:0; font-size:24pt; font-weight:bold; }
    .header-text .meta { font-size:10pt; color:#666; }

    .section-title { font-size:18pt; margin:30px 0 10px; border-bottom:2px solid #666; padding-bottom:5px; }
    table { width:100%; border-collapse:collapse; margin-bottom:20px; }
    table th, table td { border:1px solid #ccc; padding:8px 10px; vertical-align:middle; text-align:left; }
    table th { background:#f5f5f5; }
    .skills-table th { background:#28a745; color:#fff; text-align:center; }
    .skills-table td { text-align:center; }

    .footer { text-align:center; font-size:10pt; color:#666; margin-top:40px; }
    .arabic-text { font-family:"Cairo",sans-serif; direction:rtl; text-align:right; margin-top:30px; }
  </style>
</head>
<body onload="window.print()">

  <!-- Top wave -->
  <div class="header-wave">
    <svg viewBox="0 0 500 280" preserveAspectRatio="none">
      <path d="M0,100 C150,280 350,-80 500,100 L500,0 L0,0 Z" fill="#efefef"/>
    </svg>
  </div>

  <!-- Bottom wave -->
  <div class="footer-wave">
    <svg viewBox="0 0 500 250" preserveAspectRatio="none">
      <path d="M0,80 C150,220 350,-100 500,80 L500,0 L0,0 Z" fill="#efefef"/>
    </svg>
  </div>

  <div class="container">
    <!-- Header -->
    <div class="header">
      <div class="header-left">
        <img src="{{ asset('images/logo.svg') }}" alt="Logo">
      </div>
      <div class="header-text">
        <h1 class="title">Exam #{{ $exam->id }} Details</h1>
        <div class="meta">
          Printed by: {{ auth()->user()->name }}<br>
          Printed on: {{ now()->format('Y-m-d H:i') }}<br>
          Exam date & time: {{ $exam->exam_date->format('Y-m-d') }} at {{ date(' h:i A', strtotime($exam->time)) ?? '-' }}
        </div>
      </div>
    </div>

    <!-- Basic Info -->
    <div>
      <table>
        <tr>
          <th>Exam Type</th>
          <td>{{ ucfirst($exam->exam_type) }}</td>
          <th>Course Code </th>
         <td>
            <strong>(#{{ $exam->course->id }})</strong>
            @if($exam->course->courseType) / {{ $exam->course->courseType->name }} @endif
            @if($exam->course->groupType)  / {{ $exam->course->groupType->name  }} @endif
         </td>
        </tr>
        <tr>
          <th>Examiner</th>
          <td>{{ optional($exam->examiner)->name ?? 'Unassigned' }}</td>
          <th>Instructor</th>
          <td>{{ optional($exam->course->instructor)->name ?? '-' }}</td>
        </tr>
        <tr>
          <th>Course</th>
          <td>#{{ $exam->course->id }} — {{ optional($exam->course->courseType)->name }}</td>
          <th>Group Type</th>
          <td>{{ optional($exam->course->groupType)->name }}</td>
        </tr>
      </table>
    </div>

    <!-- Skills & Max Grades -->
    <div class="section-title">Skills & Maximum Grades</div>
    <table class="skills-table">
      <thead>
        <tr>
          @foreach($exam->course->courseType->skills as $skill)
            <th>{{ ucfirst($skill->name) }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        <tr>
          @foreach($exam->course->courseType->skills as $skill)
            <td>
            @if($exam->exam_type==='pre')
              {{ $skill->pivot->pre_max }}
            @elseif($exam->exam_type==='mid')
              {{ $skill->pivot->mid_max }}
            @else
              {{ $skill->pivot->final_max }}
            @endif
            </td>
          @endforeach
        </tr>
      </tbody>
    </table>

    <!-- Students & Grades -->
    <div class="section-title">Enrolled Students & Grades</div>
    @php
      // gather students who are still ongoing
      $students = $exam->course->students()->wherePivot('status','ongoing')->get();
    @endphp
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Student Name</th>
          @foreach($exam->course->courseType->skills as $skill)
            <th>{{ ucfirst($skill->name) }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @foreach($students as $i => $student)
          @php
            // find pivot record for this exam & student
            $es = $exam->examStudents->firstWhere('student_id',$student->id);
          @endphp
          <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $student->name }}</td>
            @foreach($exam->course->courseType->skills as $skill)
              @php
                $gradeRec = $es?->grades->firstWhere('course_type_skill_id',$skill->id);
              @endphp
              <td>{{ $gradeRec->grade ?? '-' }}</td>
            @endforeach
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="footer">
      © {{ date('Y') }} The Goat Academy. All rights reserved.
    </div>
  </div>
</body>
</html>
