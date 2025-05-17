{{-- resources/views/instructor/courses/student_stats_print.blade.php --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Performance Timeline — {{ $student->name }}</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  {{-- Chart.js --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* إخفاء كل ما ليس للطباعة */
    @media print {
      body * { visibility: hidden; }
      #printable, #printable * { visibility: visible; }
      #printable { position: absolute; left: 0; top: 0; width: 100%; }
    }

    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 1rem;
      background: #fff;
      color: #333;
    }
    .header {
      text-align: center;
      margin-bottom: 1rem;
    }
    .header h1 {
      font-size: 2rem;
      margin: .2rem 0;
    }
    .header h2 {
      font-size: 1.2rem;
      color: #555;
      margin: 0;
    }
    .stats-card {
      background: #f9f9f9;
      padding: 1rem;
      border-radius: .5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .stats-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 1.5rem;
    }
    .stats-table th,
    .stats-table td {
      border: 1px solid #ccc;
      padding: .5rem;
      text-align: left;
    }
    .stats-table th {
      background: #eaeaea;
      font-weight: bold;
    }
    .stats-table tr:nth-child(even) {
      background: #f5f5f5;
    }
    .footer {
      text-align: center;
      font-size: .9rem;
      color: #888;
      margin-top: 2rem;
    }
  </style>
</head>
<body>
  <div id="printable">
    <div class="header">
      <h1>Performance Timeline</h1>
      <h2>Student: <strong>{{ $student->name }}</strong></h2>
    </div>

    <div class="stats-card">
      <canvas id="studentTimeline" height="120"></canvas>
    </div>

    <table class="stats-table">
      <thead>
        <tr>
          <th style="width:40px">#</th>
          <th>Stage</th>
          <th style="width:100px;text-align:right">Score (%)</th>
        </tr>
      </thead>
      <tbody>
        @foreach($timeline as $i => $row)
          <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $row['label'] }}</td>
            <td style="text-align:right">
              @if(is_null($row['score']))
                —
              @else
                {{ $row['score'] }}%
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="footer">Generated on {{ now()->format('Y-m-d H:i') }}</div>
  </div>

  <script>
    // رسم المخطط
    const ctx = document.getElementById('studentTimeline').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: {!! json_encode(array_column($timeline, 'label')) !!},
        datasets: [{
          label: 'Score %',
          data: {!! json_encode(array_column($timeline, 'score')) !!},
          fill: true,
          backgroundColor: 'rgba(111,66,193,0.2)',
          borderColor: '#6f42c1',
          tension: 0.3,
          pointBackgroundColor: '#fff',
          pointBorderColor: '#6f42c1',
          pointRadius: 5
        }]
      },
      options: {
        scales: {
          y: {
            suggestedMin: 0,
            suggestedMax: 100,
            ticks: { stepSize: 10 }
          }
        },
        plugins: { legend: { display: false } },
        animation: { duration: 1000 }
      }
    });

    // تأخير 3 ثوانٍ قبل الطباعة ثم إعادة التوجيه للخلف بعد الطباعة
    setTimeout(() => {
      window.print();
    }, 1000);
  </script>
</body>
</html>
