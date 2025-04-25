{{-- resources/views/exam_officer/courses/print.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Courses List – The Goat Academy</title>

    {{-- Cairo (محلّي) + أي خط احتياطي --}}
    <style>
        @font-face{
            font-family:'Cairo';
            src:url("file://{{ public_path('fonts/Cairo-Regular.ttf') }}") format('truetype');
            font-weight:400;
        }
        @font-face{
            font-family:'Cairo';
            src:url("file://{{ public_path('fonts/Cairo-Bold.ttf') }}") format('truetype');
            font-weight:700;
        }

        /* صفحة A4 بلا هوامش */
        @page   { size:210mm 297mm; margin:0 }
        html,body{
            width:210mm;height:297mm;margin:0;padding:0;
            background:#fff;
            font-family:'Cairo',sans-serif;font-size:13px;color:#333;
        }
        @media print{
            *{-webkit-print-color-adjust:exact !important;print-color-adjust:exact !important}
        }

        /* حاوية مرنة تملأ الصفحة */
        .container{
            display:flex;flex-direction:column;min-height:100%;
            padding:12mm 10mm;box-sizing:border-box;position:relative;
        }

        /* موجات التزيين */
        .header-wave,.footer-wave{position:absolute;width:100%;z-index:-1;left:0}
        .header-wave{top:0;height:260px}
        .footer-wave{bottom:0;height:230px;transform:rotate(180deg)}

        /* رأس الصفحة */
        .title{font-size:22pt;font-weight:700;margin:0}
        .subtitle{font-size:14pt;color:#666;margin:4px 0 0}
        .prep{font-size:12px;margin-top:6px;text-align:center}

        /* جدول مضغوط بعرض ثابت ومُوسَّط */
        table{
            width:auto;                /* لا يشغل الصفحة كلها */
            max-width:160mm;           /* ≈ 600 px */
            margin:12mm auto 0;        /* توسيط أفقيًا */
            border-collapse:collapse;
            font-size:11px;
        }
        th,td{border:1px solid #444;padding:5px 6px;text-align:center}
        th{background:#f2f2f2}
        .table-danger{background:#fbb4b4 !important}

        .section-title{
            font-size:16pt;margin:20px 0 6px;
            border-bottom:2px solid #666;padding-bottom:4px;text-align:left
        }

        .footer{margin-top:auto;text-align:left;font-size:11px}
    </style>
</head>
<body>

  {{-- موجة أعلى --}}
  <div class="header-wave">
      <svg viewBox="0 0 500 260" preserveAspectRatio="none">
          <path d="M0,100 C150,260 350,-80 500,100 L500,0 L0,0 Z" fill="#efefef"/>
      </svg>
  </div>

  {{-- موجة أسفل --}}
  <div class="footer-wave">
      <svg viewBox="0 0 500 230" preserveAspectRatio="none">
          <path d="M0,80 C150,220 350,-100 500,80 L500,0 L0,0 Z" fill="#efefef"/>
      </svg>
  </div>

  <div class="container">

      {{-- HEADER --}}
      <div style="text-align:center">
          <img src="{{ public_path('images/logo.svg') }}" alt="Logo" style="width:140px;margin-bottom:8px">
          <h1 class="title">The Goat Academy</h1>
          <p  class="subtitle">Courses List</p>
          <div class="prep">
              Prepared by: {{ auth()->user()->name ?? 'N/A' }}<br>
              Printed on: {{ now()->format('Y-m-d H:i:s') }}
          </div>
      </div>

      {{-- SECTION TITLE --}}
      <div class="section-title">Courses Schedule</div>

      {{-- CONTENT --}}
      @if($courses->isEmpty())
          <p style="text-align:center;margin-top:30mm;font-size:16pt;color:#d00">
              لا توجد امتحانات مجدولة لهذا اليوم
          </p>
      @else
          <table>
              <thead>
                <tr>
                  <th style="width:10mm">ID</th>
                  <th style="width:25mm">Time</th>
                  <th style="width:27mm">Days</th>
                  <th style="width:28mm">Pre&nbsp;Test</th>
                  <th style="width:28mm">Mid&nbsp;Test</th>
                  <th style="width:28mm">Final&nbsp;Test</th>
                </tr>
              </thead>
              <tbody>
              @foreach($courses as $course)
                  @php
                      [$start,$end] = explode(' - ', $course->time);
                      $fs = Carbon\Carbon::createFromFormat('H:i',$start)->format('h:i A');
                      $fe = Carbon\Carbon::createFromFormat('H:i',$end)->format('h:i A');
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
