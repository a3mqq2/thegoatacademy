<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Courses List - The Goat Academy</title>
  <!-- Google Fonts for English (Poppins) and Arabic (Cairo) -->
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Poppins:wght@400;700&display=swap" rel="stylesheet" />
  <style>
    /* Remove default page margins for A4 printing */
    @page { size: A4; margin: 0; }
    body { font-family: "Poppins", Arial, sans-serif; font-size:14px; margin:0; padding:0; color:#333; }
    .container { position:relative; width:100%; min-height:100vh; padding:11mm 10mm; box-sizing:border-box; }
    .header-wave, .footer-wave { position:absolute; width:100%; z-index:-1; }
    .header-wave { top:0; height:280px; }
    .footer-wave { bottom:0; height:250px; transform:rotate(180deg); }
    .header, .footer { text-align:center; }
    .header-content { display:flex; align-items:center; margin-bottom:20px; }
    .header-left img { width:200px; margin-right:20px; }
    .title { font-size:26pt; margin:0; font-weight:bold; }
    .subtitle { font-size:16pt; color:#666; margin:5px 0 0; }
    .prepared-info { font-size:14px; margin-top:10px; }
    table { width:100%; border-collapse:collapse; margin-top:20px; }
    table th, table td { border:1px solid #444; padding:8px 10px; text-align:center; }
    table th { background:#f2f2f2; }
    .table-success { background-color: #d4edda !important; } /* bootstrapsuccess */
    .section-title { font-size:18pt; margin:30px 0 10px; border-bottom:2px solid #666; padding-bottom:5px; }
    @media print { body{font-size:12pt;} .container{padding:10mm;} }
  </style>
</head>
<body onload="window.print()">

  {{-- TOP WAVE --}}
  <div class="header-wave">
    <svg viewBox="0 0 500 280" preserveAspectRatio="none">
      <path d="M0,100 C150,280 350,-80 500,100 L500,0 L0,0 Z" fill="#efefef" stroke="none"/>
    </svg>
  </div>

  {{-- BOTTOM WAVE --}}
  <div class="footer-wave">
    <svg viewBox="0 0 500 250" preserveAspectRatio="none">
      <path d="M0,80 C150,220 350,-100 500,80 L500,0 L0,0 Z" fill="#efefef" stroke="none"/>
    </svg>
  </div>

  <div class="container">
    {{-- HEADER --}}
    <div class="header">
      <div class="header-content">
        <div class="header-left">
          <img src="{{ asset('images/logo.svg') }}" alt="Logo">
          <div class="header-text">
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

    {{-- TITLE --}}
    <div class="section-title">Courses Schedule</div>

    {{-- TABLE --}}
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Time</th>
            <th>Days</th>
            <th>Pre Test Date</th>
            <th>Mid Test Date</th>
            <th>Final Test Date</th>
          </tr>
        </thead>
        <tbody>
          @php
            use Carbon\Carbon;
            $today     = Carbon::today()->toDateString();
            $startWeek = Carbon::today()->startOfWeek(Carbon::SATURDAY)->toDateString();
            $endWeek   = Carbon::today()->endOfWeek(Carbon::FRIDAY)->toDateString();
            $afterOne  = Carbon::today()->addDay()->toDateString();
            $afterTwo  = Carbon::today()->addDays(2)->toDateString();
            $schedule  = request('schedule','');
          @endphp

          @foreach($courses as $course)
            @php
              [$start,$end] = explode(' - ', $course->time);
              $fs = Carbon::createFromFormat('H:i',$start)->format('h:i A');
              $fe = Carbon::createFromFormat('H:i',$end  )->format('h:i A');

              // helper to decide if a given date matches the schedule
              $match = fn($d) => match($schedule) {
                'daily'      => $d === $today,
                'weekly'     => $d >= $startWeek && $d <= $endWeek,
                'afterADay'  => $d === $afterOne,
                'afterTwoDays' => $d === $afterTwo,
                default      => false,
              };

              // compute classes
              $preCls   = $course->pre_test_date   && $match($course->pre_test_date)   ? 'table-success' : '';
              $midCls   = $course->mid_exam_date   && $match($course->mid_exam_date)   ? 'table-success' : '';
              $finalCls = $course->final_exam_date && $match($course->final_exam_date) ? 'table-success' : '';
            @endphp

            <tr>
              <td>{{ $course->id }}</td>
              <td>{{ $fs }} – {{ $fe }}</td>
              <td>{{ $course->days }}</td>
              <td class="{{ $preCls   }}">{{ $course->pre_test_date   ?? '-' }}</td>
              <td class="{{ $midCls   }}">{{ $course->mid_exam_date   ?? '-' }}</td>
              <td class="{{ $finalCls }}">{{ $course->final_exam_date ?? '-' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
      © {{ date('Y') }} The Goat Academy. All rights reserved.
    </div>
  </div>
</body>
</html>
