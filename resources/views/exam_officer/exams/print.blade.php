<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Print Exam #{{ $exam->id }}</title>

  {{-- Bootstrap CDN --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
  <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}" />

  <style>
    /* Import Google Fonts */
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Poppins:wght@400;700&display=swap');

    /* 1. Page size */
    @page {
      size: 90mm 90mm;
      margin: 0;
    }
    html, body {
      width: 90mm;
      height: 90mm;
      margin: 0;
      padding: 0;
      color: #fff;
    }

    /* 2. Force color printing */
    @media print {
      * {
        -webkit-print-color-adjust: exact !important;
        -moz-print-colors: exact !important;
        print-color-adjust: exact !important;
      }
    }

    /* 3. Full-page background */
    body {
      background: url("{{ asset('images/exam.png') }}") no-repeat center center;
      background-size: 100% 100%;
      -webkit-print-color-adjust: exact;
      color-adjust: exact;
      font-family: 'Poppins', Arial, sans-serif;
      font-size: 14px;
    }

    /* 4. Container */
    .container {
      box-sizing: border-box;
      width: 100%;
      height: 100%;
      position: relative;
    }

    /* 5. Title */
    .title {
      text-align: center;
      font-weight: bold;
      font-size: 12px;
      position: absolute;
      width: 306px;
      top: 50px;
      left: 14px;
      text-transform: uppercase;
      padding: 4px;
      border-radius: 30px;
    }

    /* 6. Examiner & time & date */
    .examiner, .time {
      position: absolute;
      font-size: 8px;
      font-weight: normal;
      color: #fff;
    }
    .examiner { top: 83px; left: 28px; width: 140px; }
    .time     { top: 81px; right: 37px; }
    .examiner.date { top: 97px; left: 28px; }
    .time.date     { top: 95px; right: 37px; }

    /* 7. Table text */
    th, td {
      font-size: 8px !important;
      background: #000 !important;
      color: #fff !important;
      padding: 5px !important;
    }
  </style>
</head>
<body onload="window.print()">
  <div class="container">
    <!-- Header Title -->
    <h1 class="title">{{ ucfirst($exam->course->courseType->name) }} {{ ucfirst($exam->exam_type) }} - EXAM RESULTS (#{{ $exam->id }})</h1>

    <!-- Examiner -->
    <div class="examiner">
      <i class="fa fa-user"></i>
      @if($exam->examiner)
        @if($exam->examiner->gender == 'male') Mr. @else Mrs. @endif
      @else
        Mr.
      @endif
      : {{ optional($exam->examiner)->name ?? 'Unassigned' }}
    </div>

    <!-- Time -->
    <div class="time">
      <i class="fa fa-clock"></i>
      Time: {{ \Carbon\Carbon::parse($exam->time)->format('h:i A') }}
    </div>

    <!-- Days -->
    <div class="examiner date">
      <i class="fa fa-table"></i>
      : {{ $exam->course->days ?? '' }}
    </div>

    <!-- Date -->
    <div class="time date">
      <i class="fa fa-calendar"></i>
      Date: {{ \Carbon\Carbon::parse($exam->exam_date)->format('Y-m-d') }}
    </div>

    @php
      $skills  = $exam->course->courseType->skills;
      $ongoing = $exam->course->students()->wherePivot('status','ongoing')->get();
    @endphp

    <table class="table table-dark text-light table-bordered"
           style="width:90%; position:absolute; top:114px;">
      <thead>
        <tr>
          <th>NO</th>
          <th>NAME</th>
          @foreach($skills as $skill)
            <th class="text-center">{{ mb_substr($skill->name,0,1,'UTF-8') }}</th>
          @endforeach
          <th class="text-center">PER</th>
        </tr>
      </thead>
      <tbody>
        @foreach($ongoing as $i => $student)
          @php
            $es = $exam->examStudents->firstWhere('student_id',$student->id);
            $grades = []; $maxes = [];
            foreach($skills as $skill) {
              $g = optional($es?->grades->firstWhere('course_type_skill_id',$skill->id))->grade ?: 0;
              $m = $exam->exam_type==='pre'  ? $skill->pivot->pre_max
                   : ($exam->exam_type==='mid'  ? $skill->pivot->mid_max
                                               : $skill->pivot->final_max);
              $grades[] = $g;
              $maxes[]  = $m;
            }
            $sumG = array_sum($grades);
            $sumM = array_sum($maxes);
            $per  = $sumM>0 ? round($sumG/$sumM*100,1) : 0;
          @endphp
          <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $student->name }}</td>
            @foreach($grades as $g)
              <td class="text-center">{{ $g }}</td>
            @endforeach
            <td class="text-center {{ $per>=50 ? 'text-success':'text-danger' }}">
              {{ $per }}%
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</body>
</html>
