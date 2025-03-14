{{-- resources/views/admin/students/print_suggestion_courses.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Suggested Courses</title>
  <!-- Google Fonts for English (Poppins) and Arabic (Cairo) -->
  <link
    href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Poppins:wght@400;700&display=swap"
    rel="stylesheet"
  />

  <style>
    /* A4 page, no default margins so wave shapes can fill edges */
    @page {
      size: A4;
      margin: 0;
    }

    /* Use Poppins for general content */
    body {
      font-family: "Poppins", Arial, sans-serif;
      font-size: 14px;
      margin: 0;
      padding: 0;
      color: #333;
      background: #fff;
    }

    .container {
      position: relative;
      width: 100%;
      min-height: 100vh; /* ensure it fills the whole page */
      box-sizing: border-box;
      overflow: hidden; /* hide overflow from large waves */
      /* Internal padding so text won't clash with wave shapes */
      padding: 11mm 10mm;
    }

    /* ========== TOP WAVE ========== */
    .header-wave {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 280px; /* wave height for top */
      z-index: -1;
    }
    .header-wave svg {
      display: block;
      width: 100%;
      height: 100%;
    }

    /* ========== BOTTOM WAVE ========== */
    .footer-wave {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 250px; /* wave height for bottom */
      z-index: -1;
      transform: rotate(180deg);
    }
    .footer-wave svg {
      display: block;
      width: 100%;
      height: 100%;
    }

    /* ========== HEADER ========== */
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

    /* Table styling for the main content */
    .suggestions table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    .suggestions table th,
    .suggestions table td {
      border: 1px solid #ccc;
      padding: 8px 10px;
      vertical-align: top;
      text-align: left;
    }
    .suggestions table th {
      background: #f5f5f5;
    }

    /* Footer text styling */
    .footer {
      position: relative;
      text-align: center;
      font-size: 12px;
      color: #666;
    }
  </style>
</head>

<body onload="window.print()">
  <!-- Top wave shape -->
  <div class="header-wave">
    <svg viewBox="0 0 500 280" preserveAspectRatio="none">
      <!-- Customize path/color as you like -->
      <path d="M0,100 C150,280 350,-80 500,100 L500,0 L0,0 Z"
            style="stroke: none; fill: #efefef;"></path>
    </svg>
  </div>

  <!-- Bottom wave shape -->
  <div class="footer-wave">
    <svg viewBox="0 0 500 250" preserveAspectRatio="none">
      <path d="M0,80 C150,220 350,-100 500,80 L500,0 L0,0 Z"
            style="stroke: none; fill: #efefef;"></path>
    </svg>
  </div>

  <div class="container">

    <!-- HEADER -->
    <div class="header">
      <div class="header-content">
        <div class="header-left">
          <!-- Replace with your actual logo path -->
          <img src="{{ asset('images/logo.svg') }}" alt="Logo">
          <div class="header-text">
            <h1 class="title">Suggested Courses</h1>
            <p class="subtitle">for {{ $student->name }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- SUGGESTED COURSES TABLE -->
    <div class="suggestions">
      <h2 style="font-size:20pt; margin:0 0 8px; padding-bottom:5px; border-bottom:2px solid #666;">
        All Matching Courses
      </h2>
      @if($courses->count())
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Course Type</th>
              <th>Group Type</th>
              <th>Instructor</th>
              <th>Status</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Students Count</th>
            </tr>
          </thead>
          <tbody>
            @foreach($courses as $index => $course)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $course->courseType?->name ?? 'N/A' }}</td>
                <td>{{ $course->groupType?->name ?? 'N/A' }}</td>
                <td>{{ $course->instructor?->name ?? 'N/A' }}</td>
                <td>
                  <span class="badge bg-{{ 
                    $course->status === 'upcoming'  ? 'warning' : (
                    $course->status === 'ongoing'   ? 'info'    : (
                    $course->status === 'completed' ? 'success' : (
                    $course->status === 'cancelled' ? 'danger'  : 'secondary')))
                  }}">
                    {{ ucfirst($course->status) }}
                  </span>
                </td>
                <td>{{ $course->start_date }}</td>
                <td>{{ $course->end_date }}</td>
                <td>{{ $course->students_count }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        <div style="margin-top: 15px;">
          <p class="text-muted">No matching courses found.</p>
        </div>
      @endif
    </div>

    <!-- FOOTER -->
    <div class="footer">
      © 2025 The Goat Academy. All rights reserved.
    </div>
  </div>

  <script>
    // After the print dialog closes, redirect user back to the student's profile
    window.onafterprint = function() {
      window.location.href = "{{ route('admin.students.show', $student->id) }}";
    };
  </script>
</body>
</html>
