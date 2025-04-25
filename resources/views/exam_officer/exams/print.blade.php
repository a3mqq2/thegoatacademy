{{-- resources/views/exam_officer/exams/card.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>Exam #{{ $exam->id }}</title>

<style>
@font-face{
  font-family:'Cairo';
  src:url("file://{{ public_path('fonts/Cairo-Regular.ttf') }}") format('truetype');
}
*{margin:0;padding:0;box-sizing:border-box}
html,body{width:340px;height:340px;font-family:'Cairo',sans-serif;color:#fff}
body{
  background:url('data:image/png;base64,{{ $bgData }}') no-repeat center/100% 100%;
}
.container{position:relative;width:100%;height:100%}
.title{position:absolute;top:50px;left:14px;width:306px;text-align:center;font-size:12px;font-weight:bold;text-transform:uppercase}
.examiner,.time{position:absolute;font-size:8px}
.examiner{top:83px;left:28px;width:140px}
.time{top:81px;right:37px}
.examiner.date{top:97px;left:28px}
.time.date{top:95px;right:37px}
table{position:absolute;top:114px;left:0;width:100%;border-collapse:collapse}
th,td{font-size:8px;padding:5px;background:#000;border:1px solid #333;text-align:center}
</style>
</head>
<body>
<div class="container">
  <h1 class="title">{{ ucfirst($exam->course->courseType->name) }}
      {{ ucfirst($exam->exam_type) }} – Exam Results (#{{ $exam->id }})</h1>

  <div class="examiner">👤
    {{ optional($exam->examiner)->gender=='male'?'Mr.':'Mrs.' }}
    {{ optional($exam->examiner)->name ?? 'Unassigned' }}
  </div>
  <div class="time">⏰ {{ \Carbon\Carbon::parse($exam->time)->format('h:i A') }}</div>
  <div class="examiner date">📅 {{ $exam->course->days }}</div>
  <div class="time date">📆 {{ \Carbon\Carbon::parse($exam->exam_date)->format('Y-m-d') }}</div>

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
          $es = $exam->examStudents->firstWhere('student_id',$student->id);
          $grades=[];$maxes=[];
          foreach($skills as $skill){
            $g = optional($es?->grades->firstWhere('course_type_skill_id',$skill->id))->grade?:0;
            $m = $exam->exam_type=='pre'  ? $skill->pivot->pre_max :
                 ($exam->exam_type=='mid' ? $skill->pivot->mid_max  : $skill->pivot->final_max);
            $grades[]=$g;$maxes[]=$m;
          }
          $per = array_sum($maxes)?round(array_sum($grades)/array_sum($maxes)*100,1):0;
        @endphp
        <tr>
          <td>{{ $i+1 }}</td>
          <td>{{ $student->name }}</td>
          @foreach($grades as $g)<td>{{ $g }}</td>@endforeach
          <td style="color:{{ $per>=50?'#0f0':'#f00' }}">{{ $per }}%</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
</body>
</html>
