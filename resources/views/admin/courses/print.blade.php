<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Course Outline</title>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Poppins:wght@400;700&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

  <style>
    @page {
      size: A4;
      margin: 0;
    }

    body {
      font-family: "Poppins", Arial, sans-serif;
      font-size: 14px;
      margin: 0;
      padding: 0;
      color: #333;
      background: #fff;
    }

    #pdf-content {
      position: relative;
      width: 100%;
      min-height: 100vh;
      box-sizing: border-box;
      overflow: hidden;
      padding: 11mm 10mm;
    }

    .header-wave,
    .footer-wave {
      position: absolute;
      left: 0;
      width: 100%;
      z-index: -1;
    }

    .header-wave {
      top: 0;
      height: 280px;
    }

    .footer-wave {
      bottom: 0;
      height: 250px;
      transform: rotate(180deg);
    }

    .header-wave svg,
    .footer-wave svg {
      display: block;
      width: 100%;
      height: 100%;
    }

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

    .course-details {
      margin-bottom: 25px;
    }

    .course-details h2 {
      font-size: 20pt;
      margin: 0 0 8px;
      padding-bottom: 5px;
      border-bottom: 2px solid #666;
    }

    .course-details table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    .course-details table th,
    .course-details table td {
      border: 1px solid #ccc;
      padding: 8px 10px;
      vertical-align: top;
      text-align: left;
    }

    .course-details table th {
      width: 30%;
      background: #f5f5f5;
    }

    .section-title {
      font-size: 18pt;
      margin: 40px 0 10px;
      border-bottom: 2px solid #666;
      padding-bottom: 5px;
    }

    .schedule,
    .students {
      margin-bottom: 25px;
    }

    .schedule table,
    .students table {
      width: 100%;
      border-collapse: collapse;
    }

    .schedule table th,
    .schedule table td,
    .students table th,
    .students table td {
      border: 1px solid #ccc;
      padding: 8px 10px;
      vertical-align: top;
      text-align: left;
    }

    .schedule table th,
    .students table th {
      background: #f5f5f5;
    }

    .footer {
      position: relative;
      text-align: center;
      font-size: 12px;
      color: #666;
      margin-top: 40px;
    }

    .arabic-text {
      font-family: "Cairo", sans-serif;
      direction: rtl;
      text-align: right;
      margin-top: 30px;
    }

    .arabic-text h3 {
      font-size: 18pt;
      margin-bottom: 15px;
      border-bottom: 2px solid #666;
      padding-bottom: 5px;
    }

    .arabic-text ul {
      list-style-type: disc;
      padding-right: 20px;
      margin: 0;
    }

    .arabic-text ul li {
      margin-bottom: 10px;
    }

    #downloadBtn {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1000;
      padding: 10px 20px;
      font-weight: bold;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
  </style>
</head>
<body>

<button id="downloadBtn" onclick="downloadPDF()">Download PDF</button>

<div id="pdf-content">

  <div class="header-wave">
    <svg viewBox="0 0 500 280" preserveAspectRatio="none">
      <path d="M0,100 C150,280 350,-80 500,100 L500,0 L0,0 Z" style="stroke: none; fill: #efefef;"></path>
    </svg>
  </div>

  <div class="footer-wave">
    <svg viewBox="0 0 500 250" preserveAspectRatio="none">
      <path d="M0,80 C150,220 350,-100 500,80 L500,0 L0,0 Z" style="stroke: none; fill: #efefef;"></path>
    </svg>
  </div>

  <div class="container">
    <div class="header">
      <div class="header-content">
        <div class="header-left">
          <img src="{{ asset('images/logo.svg') }}" alt="Logo">
          <div class="header-text">
            <h1 class="title">Course Outline #{{$course->id}} </h1>
            <p class="subtitle">All the details about the course</p>
          </div>
        </div>
      </div>
    </div>

    <div class="course-details">
      <h2>Course Details</h2>
      <table>
        <tr><th>Course Name</th><td>{{ $course->courseType->name ?? 'N/A' }}</td></tr>
        <tr><th>Group Type</th><td>{{ $course->groupType->name ?? 'N/A' }}</td></tr>
        <tr><th>Instructor</th><td>{{ $course->instructor->name ?? 'N/A' }}</td></tr>
        <tr><th>Start Date</th><td>{{ $course->start_date }}</td></tr>
        <tr><th>Participants</th><td>{{ $course->students->count() }}</td></tr>
        <tr><th>Days</th><td>{{ $course->days }}</td></tr>
        <tr><th>Time</th><td>{{ $course->time }}</td></tr>
        <tr><th>برنامج الاجتماع</th><td>{{ $course->meetingPlatform->name ?? '-' }}</td></tr>
      </table>
    </div>

    <div class="schedule">
      <div class="section-title">Schedule</div>
      <table>
        <thead>
          <tr><th>#</th><th>Day</th><th>Date</th><th>From Time</th><th>To Time</th></tr>
        </thead>
        <tbody>
          @php
            $total = $course->schedules->count();
            $midPoint = ceil($total / 2);
          @endphp

          <tr style="background-color: #007bff; color: #fff;">
            <td colspan="2">Pre test Exam</td>
            <td>{{ $course->pre_test_date }} ({{ \Carbon\Carbon::parse($course->pre_test_date)->format('l') }})</td>
            <td colspan="2"></td>
          </tr>

          @foreach ($course->schedules as $item)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $item->day }}</td>
              <td>{{ $item->date }}</td>
              <td>{{ $item->from_time }}</td>
              <td>{{ $item->to_time }}</td>
            </tr>
            @if ($loop->iteration == $midPoint)
              <tr style="background-color: #007bff; color: #fff;">
                <td colspan="2">MID exam test</td>
                <td>{{ $course->mid_exam_date }} ({{ \Carbon\Carbon::parse($course->mid_exam_date)->format('l') }})</td>
                <td colspan="2"></td>
              </tr>
            @endif
          @endforeach

          <tr style="background-color: #007bff; color: #fff;">
            <td colspan="2">Final exam test</td>
            <td>{{ $course->final_exam_date }} ({{ \Carbon\Carbon::parse($course->final_exam_date)->format('l') }})</td>
            <td colspan="2"></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="students">
      <div class="section-title">Participants</div>
      <table>
        <thead><tr><th>#</th><th>Name</th></tr></thead>
        <tbody>
          @foreach ($course->students as $student)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $student->name }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="arabic-text">
      <h3>التعليمات الواجب الالتزام بها</h3>
      @php
        $setting = App\Models\Setting::where("key", "Instructions in course outline paper")->first();
      @endphp
      @if ($setting)
        {!! $setting->value !!}
      @endif
    </div>

    <div class="footer">
      © 2025 The Goat Academy. All rights reserved.
    </div>
  </div>
</div>

<script>
  function downloadPDF() {
    const btn = document.getElementById('downloadBtn');
    btn.style.display = 'none';

    const element = document.getElementById('pdf-content');
    const opt = {
      margin: 0,
      filename: 'course-outline-{{$course->id}}.pdf',
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { scale: 2, useCORS: true },
      jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().from(element).set(opt).save().then(() => {
      btn.style.display = 'inline-block';
    });
  }
</script>

</body>
</html>
