{{-- resources/views/exam_officer/courses/card.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>Courses – {{ now()->format('Y-m-d') }}</title>

@php
    $fontRegular = 'file://'.storage_path('fonts/Cairo-Regular.ttf');
    $fontBold    = 'file://'.storage_path('fonts/Cairo-Bold.ttf');
    $bgData = base64_encode(file_get_contents(public_path('images/exam.png')));
@endphp

<style>
@font-face{font-family:'cairo';src:url('{{ $fontRegular }}') format('truetype');font-weight:400}
@font-face{font-family:'cairo';src:url('{{ $fontBold    }}') format('truetype');font-weight:700}

*{margin:0;padding:0;box-sizing:border-box}
html,body{width:340px;height:340px;font-family:'cairo',sans-serif;color:#fff}

body{
  background:url('data:image/png;base64,{{ $bgData }}') no-repeat center/100% 100%;
}

/* تخطيط البطاقة */
.container{position:relative;width:100%;height:100%}
.title{position:absolute;top:46px;left:14px;width:306px;text-align:center;
       font-size:12px;font-weight:700}

.head{position:absolute;font-size:8px}
.head.date{top:96px;left:28px}
.head.time{top:80px;right:37px}

table{position:absolute;top:114px;left:0;width:100%;border-collapse:collapse}
th,td{font-size:8px;padding:5px;background:#000;border:1px solid #333;text-align:center}
</style>
</head>
<body>
<div class="container">

  {{-- عنوان --}}
  <h1 class="title">COURSES SCHEDULE – {{ now()->format('d M Y') }}</h1>

  {{-- التاريخ والوقت --}}
  <div class="head date">⌚ {{ now()->format('H:i') }} - 📅 {{ now()->format('Y-m-d') }}</div>

  {{-- جدول --}}
  <table>
    <thead>
      <tr>
        <th>#</th><th>ID</th><th>TIME</th><th>DAYS</th>
      </tr>
    </thead>
    <tbody>
      @foreach($courses as $i=>$c)
        @php [$s,$e]=explode(' - ',$c->time); @endphp
        <tr>
          <td>{{ $i+1 }}</td>
          <td>{{ $c->id }}</td>
          <td>{{ \Carbon\Carbon::createFromFormat('H:i',$s)->format('h:i A') }}-{{ \Carbon\Carbon::createFromFormat('H:i',$e)->format('h:i A') }}</td>
          <td>{{ $c->days }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
</body>
</html>
