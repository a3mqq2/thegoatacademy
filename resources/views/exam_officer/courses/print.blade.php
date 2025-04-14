<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Courses List - The Goat Academy</title>
  <!-- Google Fonts for English (Poppins) and Arabic (Cairo) -->
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Poppins:wght@400;700&display=swap" rel="stylesheet" />
  <style>
    /* Remove default page margins for A4 printing */
    @page {
      size: A4;
      margin: 0;
    }

    /* General styles using Poppins for content */
    body {
      font-family: "Poppins", Arial, sans-serif;
      font-size: 14px;
      margin: 0;
      padding: 0;
      background: #fff;
      color: #333;
    }

    /* Container covering the full page */
    .container {
      position: relative;
      width: 100%;
      min-height: 100vh; 
      box-sizing: border-box;
      overflow: hidden;
      padding: 11mm 10mm;
    }

    /* TOP WAVE */
    .header-wave {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 280px;
      z-index: -1;
    }
    .header-wave svg {
      display: block;
      width: 100%;
      height: 100%;
    }

    /* BOTTOM WAVE */
    .footer-wave {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 250px;
      z-index: -1;
      transform: rotate(180deg);
    }
    .footer-wave svg {
      display: block;
      width: 100%;
      height: 100%;
    }

    /* HEADER STYLES */
    .header {
      position: relative;
      margin-bottom: 20px;
    }
    .header-content {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
    }
    .header-left {
      display: flex;
      align-items: center;
    }
    .header-left img {
      width: 200px;
      height: auto;
      margin-right: 20px;
    }
    .header-text .title {
      margin: 0;
      font-size: 26pt;
      font-weight: bold;
    }
    .header-text .subtitle {
      font-size: 16pt;
      color: #666;
      margin: 5px 0 0 0;
    }
    .prepared-info {
      font-size: 14px;
      margin-top: 10px;
    }

    /* TABLE STYLES */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    table th, table td {
      border: 1px solid #444;
      padding: 8px 10px;
      text-align: center;
    }
    table th {
      background-color: #f2f2f2;
    }

    /* SECTION TITLE */
    .section-title {
      font-size: 18pt;
      margin: 30px 0 10px;
      border-bottom: 2px solid #666;
      padding-bottom: 5px;
      text-align: center;
    }

    /* FOOTER */
    .footer {
      text-align: center;
      font-size: 12px;
      color: #666;
      margin-top: 20px;
    }

    /* Print specific styles */
    @media print {
      body {
        margin: 0;
        font-size: 12pt;
      }
      .container {
        padding: 10mm;
      }
      .header-wave, .footer-wave { height: auto; }
    }
  </style>
</head>
<body onload="window.print()" >
  <!-- TOP WAVE -->
  <div class="header-wave">
    <svg viewBox="0 0 500 280" preserveAspectRatio="none">
      <path d="M0,100 C150,280 350,-80 500,100 L500,0 L0,0 Z" style="stroke: none; fill: #efefef;"></path>
    </svg>
  </div>

  <!-- BOTTOM WAVE -->
  <div class="footer-wave">
    <svg viewBox="0 0 500 250" preserveAspectRatio="none">
      <path d="M0,80 C150,220 350,-100 500,80 L500,0 L0,0 Z" style="stroke: none; fill: #efefef;"></path>
    </svg>
  </div>

  <div class="container">
    <!-- HEADER SECTION -->
    <div class="header">
      <div class="header-content">
        <div class="header-left">
          <!-- Logo Image, adjust the path as needed -->
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

    <!-- COURSES TABLE SECTION -->
    <div class="section-title">Courses Schedule</div>
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
          @foreach($courses as $course)
          <tr>
            <td>{{ $course->id }}</td>
            @php
              [$start, $end] = explode(' - ', $course->time);
              $formattedStart = \Carbon\Carbon::createFromFormat('H:i', $start)->format('h:i A');
              $formattedEnd = \Carbon\Carbon::createFromFormat('H:i', $end)->format('h:i A');
            @endphp
          <td>{{ $formattedStart }} - {{ $formattedEnd }}</td>
          
            <td>{{ $course->days }}</td>
            <td>{{ $course->pre_test_date ?? '-' }}</td>
            <td>{{ $course->mid_exam_date ?? '-' }}</td>
            <td>{{ $course->final_exam_date ?? '-' }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <!-- FOOTER SECTION -->
    <div class="footer">
      © {{ date('Y') }} The Goat Academy. All rights reserved.
    </div>
  </div>
</body>
</html>
