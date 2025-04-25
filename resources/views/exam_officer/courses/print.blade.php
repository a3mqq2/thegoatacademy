<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Courses – The Goat Academy</title>

{{-- Cairo محلي --}}
<style>
 @font-face{
   font-family:'Cairo';
   src:url("file://{{ public_path('fonts/Cairo-Regular.ttf') }}") format('truetype');
 }
 @font-face{
   font-family:'Cairo';
   src:url("file://{{ public_path('fonts/Cairo-Bold.ttf') }}") format('truetype');
   font-weight:700;
 }

 /* صفحة A4 بلا هوامش */
 @page{size:210mm 297mm;margin:0}
 html,body{
   width:210mm;height:297mm;margin:0;
   font-family:'Cairo',sans-serif;font-size:14px;color:#333;
 }

 /* حاوية */
 .wrap{display:flex;flex-direction:column;min-height:100%;
       padding:12mm 10mm;box-sizing:border-box}

 .title{font-size:24pt;font-weight:700;margin:0}
 .subtitle{font-size:15pt;color:#666;margin:4px 0}
 .prep{font-size:12px;margin-top:4px;text-align:center}

 .section{font-size:17pt;border-bottom:2px solid #666;
          margin:18px 0 6px;padding-bottom:4px}

 /* ===== جدول أعرض وخط أوضح ===== */
 table{
   width:auto;
   max-width:230mm;          /* يقارب 85% من عرض الصفحة */
   margin:8mm auto 0;
   border-collapse:collapse;
   font-size:13px;
 }
 th,td{
   border:1px solid #444;
   padding:8px 8px;
   text-align:center
 }
 th{background:#f2f2f2}

 .footer{margin-top:auto;font-size:11px}
</style>
</head>
<body>
<div class="wrap">

  {{-- Header --}}
  <div style="text-align:center">
      <img src="{{ $logo ?? asset('images/logo.svg') }}" style="width:140px">
      <h1 class="title">The Goat Academy</h1>
      <p  class="subtitle">Courses List</p>
      <div class="prep">
          Prepared by: {{ auth()->user()->name ?? 'N/A' }}<br>
          Printed on: {{ now()->format('Y-m-d H:i:s') }}
      </div>
  </div>

  <div class="section">Courses Schedule</div>

  @if($courses->isEmpty())
      <p style="text-align:center;margin-top:35mm;font-size:16pt;color:#d00">
          لا توجد امتحانات مجدولة لهذا اليوم
      </p>
  @else
      <table>
         <thead>
           <tr>
             <th style="width:13mm">ID</th>
             <th style="width:32mm">Time</th>
             <th style="width:35mm">Days</th>
             <th style="width:37mm">Pre</th>
             <th style="width:37mm">Mid</th>
             <th style="width:37mm">Final</th>
           </tr>
         </thead>
         <tbody>
         @foreach($courses as $course)
           @php [$s,$e]=explode(' - ',$course->time);
                $fs=Carbon\Carbon::createFromFormat('H:i',$s)->format('h:i A');
                $fe=Carbon\Carbon::createFromFormat('H:i',$e)->format('h:i A'); @endphp
           <tr>
             <td>{{ $course->id }}</td>
             <td>{{ $fs }} – {{ $fe }}</td>
             <td>{{ $course->days }}</td>
             <td>{{ $course->pre_test_date   ?? '-' }}</td>
             <td>{{ $course->mid_exam_date   ?? '-' }}</td>
             <td>{{ $course->final_exam_date ?? '-' }}</td>
           </tr>
         @endforeach
         </tbody>
      </table>
  @endif

  <div class="footer">© {{ date('Y') }} The Goat Academy. All rights reserved.</div>
</div>
</body>
</html>
