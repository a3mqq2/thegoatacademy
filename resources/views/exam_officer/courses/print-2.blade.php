<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>Courses {{ $today->format('d-m-Y') }}</title>

@php
    $fR = 'file://'.storage_path('fonts/Cairo-Regular.ttf');
    $fB = 'file://'.storage_path('fonts/Cairo-Bold.ttf');
@endphp

<style>
@font-face{font-family:cairo;src:url('{{ $fR }}') format('truetype');font-weight:400}
@font-face{font-family:cairo;src:url('{{ $fB }}') format('truetype');font-weight:700}

*{margin:0;padding:0;box-sizing:border-box}
html,body{width:340px;height:340px;font:10px/1.3 cairo,sans-serif;color:#fff}

body{
  background:url('data:image/png;base64,{{ $bgData }}') no-repeat center/100% 100%;
}

.container{position:relative;width:100%;height:100%}
.title {
    position:absolute;
    top:10px;
    left:14px;
    width:306px;
    text-align:center;
    font:700 15px cairo;
}

.table {
    position:absolute;
    top:48px;
    left:0;
    width:100%;
}

th,td{font-size:8px;padding:4px;background:#000;border:1px solid #333;text-align:center}
.today  {background:#900!important}
</style>
</head>
<body>
<div class="container">

  <h1 class="title">COURSES&nbsp;SCHEDULE&nbsp;·&nbsp;{{ $today->format('d M Y') }}</h1>

  <table class="table">
    <thead>
      <tr>
        <th>#</th><th>ID</th><th>Course</th><th>TIME</th><th>DAYS</th>
        <th>P</th><th>M</th><th>F</th>
      </tr>
    </thead>
    <tbody>
    @if($courses->isEmpty())
        <tr>
          <td colspan="8" style="background:#000; color:#fff; font-size:12px; padding:20px;">No exams for today</td>
        </tr>
    @else
      @foreach($courses as $i => $c)
        @php
            [$s,$e] = explode(' - ',$c->time);
            $fmt    = fn($t)=>\Carbon\Carbon::createFromFormat('H:i',$t)->format('h:i A');
            $isToday = fn($d)=>$d && \Carbon\Carbon::parse($d)->isSameDay($today);
        @endphp
        <tr>
          <td>{{ $i+1 }}</td>
          <td>{{ $c->id }}</td>
          <td>{{ $c->courseType->name }}</td>
          <td>{{ $fmt($s) }}-{{ $fmt($e) }}</td>
          <td>{{ $c->days }}</td>
          <td class="{{ $isToday($c->pre_test_date)   ? 'today' : '' }}">
              {{ $c->pre_test_date ? date('m-d', strtotime($c->pre_test_date)) : '-' }}
          </td>
          <td class="{{ $isToday($c->mid_exam_date)   ? 'today' : '' }}">
              {{ $c->mid_exam_date ? date('m-d', strtotime($c->mid_exam_date)) : '-' }}
          </td>
          <td class="{{ $isToday($c->final_exam_date) ? 'today' : '' }}">
              {{ $c->final_exam_date ? date('m-d', strtotime($c->final_exam_date)) : '-' }}
          </td>
        </tr>
      @endforeach
    @endif
    </tbody>
  </table>

</div>
</body>
</html>
