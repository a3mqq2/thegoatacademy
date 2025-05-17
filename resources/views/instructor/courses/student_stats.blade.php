@extends('layouts.app')

@section('title', "Performance Stats — {$student->name}")

@push('styles')
<style>
  .stats-header {
    background: linear-gradient(135deg, #6f42c1, #007bff);
    color: #fff;
    padding: 1.5rem;
    border-radius: .5rem .5rem 0 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }
  .stats-card {
    border-radius: .5rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    overflow: hidden;
  }
  .stats-table thead th {
    background: #f7f8fa;
    border-bottom: 2px solid #e3e6f0;
  }
  .stats-table tbody tr:hover {
    background: #f1f3f5;
  }
</style>
@endpush

@section('content')
  <div class=" py-5">
    <div class="row">
      <div class="col-lg-12">
        <div class="stats-card mb-5">
          <div class="stats-header d-flex align-items-center justify-content-between">
            <h3 class="mb-0 text-white"><i class="fa fa-chart-line me-2"></i>Performance Timeline</h3>
            <span class="fs-5">Student: <strong>{{ $student->name }}</strong></span>
          </div>
          <div class="p-4 bg-white">
            <canvas id="studentTimeline" height="150"></canvas>
          </div>
        </div>

        <div class="card stats-card mb-4">
          <div class="card-body bg-white">
            <h5 class="card-title mb-4">Detailed Scores</h5>
            <div class="table-responsive">
              <table class="table table-striped table-hover stats-table">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Stage</th>
                    <th scope="col" class="text-end">Score (%)</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($timeline as $i => $row)
                    <tr>
                      <td>{{ $i + 1 }}</td>
                      <td>{{ $row['label'] }}</td>
                      <td class="text-end">
                        @if(is_null($row['score']))
                          <span class="text-muted">—</span>
                        @else
                          <strong>{{ $row['score'] }}%</strong>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <div class="text-end mt-3">
              <a href="{{ route('instructor.courses.show', $course) }}" class="btn btn-outline-primary">
                <i class="fa fa-arrow-left me-1"></i>Back to Course
              </a>
              <a href="{{ route(get_area_name().'.courses.students.stats', ['print' => 1, 'student' => $student,'course' => $course]) }}" class="btn btn-outline-danger">
               <i class="fa fa-print me-1"></i> Print
             </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
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
          pointRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            suggestedMin: 0,
            suggestedMax: 100,
            ticks: { stepSize: 10 }
          }
        },
        plugins: {
          legend: { display: false }
        }
      }
    });
  </script>
@endpush
