<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>Exam #{{ $exam->id }}</title>

@php
    $fontRegular = 'file://' . storage_path('fonts/Cairo-Regular.ttf');
    $fontBold    = 'file://' . storage_path('fonts/Cairo-Bold.ttf');
    $bgData      = base64_encode(file_get_contents(public_path('images/exam.png')));
@endphp

<style>
  @font-face {
      font-family: 'cairo';
      src: url('{{ $fontRegular }}') format('truetype');
      font-weight: 400;
  }
  @font-face {
      font-family: 'cairo';
      src: url('{{ $fontBold }}') format('truetype');
      font-weight: 700;
  }
  * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
  }
  html, body {
      width: 1024px;
      height: 1024px;
      font-family: 'cairo', sans-serif;
      color: #fff;
  }
  body {
      background: url('data:image/png;base64,{{ $bgData }}') no-repeat center center;
      background-size: cover;
  }
  .container {
      position: relative;
      width: 100%;
      height: 100%;
  }
  .title {
      position: absolute;
      top: 30px;
      left: 50%;
      transform: translateX(-50%);
      width: 90%;
      text-align: center;
      font-size: 32px;
      font-weight: 700;
  }
  .examiner, .time, .examiner.date, .time.date {
      position: absolute;
      font-size: 20px;
  }
  .examiner {
      top: 90px;
      left: 50px;
      width: 400px;
  }
  .time {
      top: 90px;
      right: 50px;
  }
  .examiner.date {
      top: 140px;
      left: 50px;
  }
  .time.date {
      top: 140px;
      right: 50px;
  }
  table {
      position: absolute;
      top: 200px;
      left: 50%;
      transform: translateX(-50%);
      width: 90%;
      border-collapse: collapse;
  }
  th, td {
      font-size: 18px;
      padding: 10px;
      background: #000;
      border: 1px solid #333;
      text-align: center;
  }
  </style>
  
</head>
<body>
<div class="container">

  <h1 class="title">
      {{ ucfirst($exam->course->courseType->name) }}
      {{ ucfirst($exam->exam_type) }} – Exam Results (#{{ $exam->course_id }})
  </h1>

  <div class="examiner">Examiner: {{ optional($exam->examiner)->name ?? 'Unassigned' }}</div>
  <div class="time">Time: {{ \Carbon\Carbon::parse($exam->time)->format('h:i A') }}</div>
  <div class="examiner date">Days: {{ $exam->course->days ?? '' }}</div>
  <div class="time date">Date: {{ \Carbon\Carbon::parse($exam->exam_date)->format('Y-m-d') }}</div>

  @php
      $skills  = $exam->course->courseType->skills;
      $ongoing = $exam->course->students()->wherePivot('status','ongoing')->get();
  @endphp

  <table>
    <thead>
      <tr>
        <th>NO</th><th>NAME</th>
        @foreach($skills as $s)
          <th>{{ mb_substr($s->name, 0, 1, 'UTF-8') }}</th>
        @endforeach
        <th>PER</th>
      </tr>
    </thead>
    <tbody>
      @foreach($ongoing as $i => $student)
        @php
          $es = $exam->examStudents->firstWhere('student_id', $student->id);
          $grades = [];
          $maxes = [];
          foreach($skills as $sk) {
             $g = optional($es?->grades->firstWhere('course_type_skill_id', $sk->id))->grade ?: 0;
             $m = $exam->exam_type == 'pre' ? $sk->pivot->pre_max :
                  ($exam->exam_type == 'mid' ? $sk->pivot->mid_max : $sk->pivot->final_max);
             $grades[] = $g;
             $maxes[] = $m;
          }
          $per = array_sum($maxes) ? round(array_sum($grades) / array_sum($maxes) * 100, 1) : 0;
        @endphp
        <tr>
          <td>{{ $i + 1 }}</td>
          <td>{{ $student->name }}</td>
          @foreach($grades as $g)
            <td>{{ $g }}</td>
          @endforeach
          <td style="color: {{ $per >= 50 ? '#0f0' : '#f00' }}">{{ $per }}%</td>
        </tr>
      @endforeach
    </tbody>
  </table>

</div>
</body>
</html>
