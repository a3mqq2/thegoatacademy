{{-- resources/views/exam_officer/courses/print.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Courses List – The Goat Academy</title>

    {{-- خط Cairo محلـي حتى يعمل داخل Dompdf --}}
    <style>
        @font-face{
            font-family:'Cairo';
            src:url("file://{{ storage_path('fonts/Cairo-Regular.ttf') }}") format('truetype');
            font-weight:400;
        }
        @font-face{
            font-family:'Cairo';
            src:url("file://{{ storage_path('fonts/Cairo-Bold.ttf') }}") format('truetype');
            font-weight:700;
        }

        /* صفحة A4 بلا هوامش */
        @page{size:210mm 297mm;margin:0}

        html,body{
            width:210mm;height:297mm;margin:0;padding:0;
            background:#fff;
            font-family:'Cairo',sans-serif;font-size:14px;color:#333;
        }

        @media print{*{-webkit-print-color-adjust:exact !important;print-color-adjust:exact !important}
                     body{font-size:12pt}}

        /* حاوية مرنة تملأ كامل الصفحة */
        .container{
            display:flex;flex-direction:column;
            min-height:100%;
            padding:11mm 10mm;
            box-sizing:border-box;
            position:relative;
        }

        /* موجات تزيينية */
        .header-wave,.footer-wave{position:absolute;width:100%;z-index:-1;left:0}
        .header-wave{top:0;height:280px}
        .footer-wave{bottom:0;height:250px;transform:rotate(180deg)}

        /* رأس الصفحة */
        .header{text-align:center}
        .header-content{display:flex;align-items:center;margin-bottom:20px}
        .header-left img{width:200px;margin-right:20px}
        .title{font-size:26pt;font-weight:bold;margin:0}
        .subtitle{font-size:16pt;color:#666;margin:5px 0 0}
        .prepared-info{font-size:14px;margin-top:10px}

        .section-title{font-size:18pt;margin:30px 0 10px;border-bottom:2px solid #666;padding-bottom:5px}

        /* الجدول */
        table{width:100%;border-collapse:collapse;margin-top:20px}
        th,td{border:1px solid #444;padding:8px 10px;text-align:center}
        th{background:#f2f2f2}
        .table-danger{background:#fbb4b4 !important}

        /* يجعل الفوتر يلتصق بالأسفل */
        .footer{margin-top:auto;text-align:left;font-size:12px}
    </style>
</head>
<body>

  {{-- موجة أعلى --}}
  <div class="header-wave">
      <svg viewBox="0 0 500 280" preserveAspectRatio="none">
          <path d="M0,100 C150,280 350,-80 500,100 L500,0 L0,0 Z" fill="#efefef"/>
      </svg>
  </div>

  {{-- موجة أسفل --}}
  <div class="footer-wave">
      <svg viewBox="0 0 500 250" preserveAspectRatio="none">
          <path d="M0,80 C150,220 350,-100 500,80 L500,0 L0,0 Z" fill="#efefef"/>
      </svg>
  </div>

  <div class="container">
      {{-- HEADER --}}
      <div class="header">
          <div class="header-content">
              <div class="header-left">
                  <img src="{{ public_path('images/logo.svg') }}" alt="Logo">
                  <div>
                      <h1 class="title">The Goat Academy</h1>
                      <p class="subtitle">Courses List</p>
                  </div>
              </div>
          </div>

          <div class="prepared-info">
              Prepared by: {{ auth()->user()->name ?? 'N/A' }}<br>
              Printed on: {{ now()->format('Y-m-d H:i:s') }}
          </div>
      </div>

      {{-- SECTION TITLE --}}
      <div class="section-title">Courses Schedule</div>

      {{-- CONTENT --}}
      @if($courses->isEmpty())
          <p style="text-align:center;margin-top:40mm;font-size:18pt;color:#d00">
              لا توجد امتحانات مجدولة لهذا اليوم
          </p>
      @else
          <table>
              <thead>
                  <tr>
                      <th>ID</th><th>Time</th><th>Days</th>
                      <th>Pre Test Date</th><th>Mid Test Date</th><th>Final Test Date</th>
                  </tr>
              </thead>
              <tbody>
              @php
                  use Carbon\Carbon;
              @endphp
              @foreach($courses as $course)
                  @php
                      [$start,$end] = explode(' - ', $course->time);
                      $fs = Carbon::createFromFormat('H:i',$start)->format('h:i A');
                      $fe = Carbon::createFromFormat('H:i',$end)->format('h:i A');
                  @endphp
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

      {{-- FOOTER --}}
      <div class="footer">
          © {{ date('Y') }} The Goat Academy. All rights reserved.
      </div>
  </div>
</body>
</html>
