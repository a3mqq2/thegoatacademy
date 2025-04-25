<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Print Results #{{ $exam->id }}</title>

  {{-- Bootstrap (محلي لتجنّب الحجب في dompdf) --}}
  <link rel="stylesheet" href="css/bootstrap.min.css"/>

  {{-- أيقونات FontAwesome (اختياري) --}}
  <link rel="stylesheet" href="fonts/fontawesome.css"/>

  <style>
    /* Google Fonts */
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Poppins:wght@400;700&display=swap');

    * { margin:0;padding:0; }
    html,body{ width:340px;height:340px; }

    /* A4 أو أي مقاس مطبوع */
    @page   { margin:0; size:90mm 90mm; }
    html,
    body    { width:90mm;height:90mm;margin:0;padding:0;font-family:"Poppins","Cairo",sans-serif;color:#fff }

    /* إجبار الطابعة على الألوان */
    @media print{
        *{-webkit-print-color-adjust:exact !important;print-color-adjust:exact !important}
    }

    /* خلفية كاملة (استعمل مسار ملف محلي) */
    body{
        background:url("/images/exam.png") no-repeat center center;
        background-size:100% 100%;
    }

    .container{position:relative;width:100%;height:100%}
    .title{position:absolute;top:50px;left:14px;width:306px;text-align:center;font-size:12px;font-weight:bold;text-transform:uppercase}
    .examiner,.time{position:absolute;font-size:8px}
    .examiner{top:83px;left:28px;width:140px}
    .time{top:81px;right:37px}
    .examiner.date{top:97px;left:28px}
    .time.date{top:95px;right:37px}

    table{position:absolute;top:114px;left:0;width:100%}
    th,td{font-size:8px;padding:5px;background:#000;color:#fff;border:1px solid #333}
  </style>
</head>
<body>
<div class="container">

  {{-- عنوان --}}
  <h1 class="title">
    {{ ucfirst($exam->course->courseType->name) }}
    {{ ucfirst($exam->exam_type) }} - EXAM RESULTS (#{{ $exam->id }})
  </h1>

  {{-- الممتحِن --}}
  <div class="examiner">
      <i class="fa fa-user"></i>
      @if($exam->examiner)
          {{ $exam->examiner->gender=='male'?'Mr.':'Mrs.' }}
      @else
          Mr.
      @endif
      : {{ optional($exam->examiner)->name ?? 'Unassigned' }}
  </div>

  {{-- الوقت --}}
  <div class="time"><i class="fa fa-clock"></i>
      {{ \Carbon\Carbon::parse($exam->time)->format('h:i A') }}
  </div>

  {{-- الأيام --}}
  <div class="examiner date"><i class="fa fa-table"></i> : {{ $exam->course->days ?? '' }}</div>

  {{-- التاريخ --}}
  <div class="time date"><i class="fa fa-calendar"></i>
      {{ \Carbon\Carbon::parse($exam->exam_date)->format('Y-m-d') }}
  </div>

  @php
      $skills  = $exam->course->courseType->skills;
      $ongoing = $exam->course->students()->wherePivot('status','ongoing')->get();
  @endphp

  <table>
      <thead>
      <tr>
          <th>NO</th><th>NAME</th>
          @foreach($skills as $skill)
              <th>{{ mb_substr($skill->name,0,1,'UTF-8') }}</th>
          @endforeach
          <th>PER</th>
      </tr>
      </thead>
      <tbody>
      @foreach($ongoing as $i=>$student)
          @php
              $es=$exam->examStudents->firstWhere('student_id',$student->id);
              $grades=[];$maxes=[];
              foreach($skills as $skill){
                 $g=optional($es?->grades->firstWhere('course_type_skill_id',$skill->id))->grade?:0;
                 $m=$exam->exam_type==='pre'?$skill->pivot->pre_max:
                    ($exam->exam_type==='mid'?$skill->pivot->mid_max:$skill->pivot->final_max);
                 $grades[]=$g;$maxes[]=$m;
              }
              $per=array_sum($maxes)?round(array_sum($grades)/array_sum($maxes)*100,1):0;
          @endphp
          <tr>
              <td>{{ $i+1 }}</td>
              <td>{{ $student->name }}</td>
              @foreach($grades as $g)<td>{{ $g }}</td>@endforeach
              <td class="{{ $per>=50?'text-success':'text-danger' }}">{{ $per }}%</td>
          </tr>
      @endforeach
      </tbody>
  </table>
</div>
</body>
</html>
