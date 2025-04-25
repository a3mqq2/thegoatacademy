@extends('layouts.app')
@section('title', 'Exam Officer Dashboard')

@push('styles')
<!-- Include ApexCharts CSS if needed -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.css">

<style>
  /* Gradient Cards */
  .gradient-card-1 {
    background: linear-gradient(135deg, #007bff 0%, #5bc0de 100%);
    color: #fff;
  }
  .gradient-card-2 {
    background: linear-gradient(135deg, #28a745 0%, #72c583 100%);
    color: #fff;
  }
  .gradient-card-3 {
    background: linear-gradient(135deg, #f0ad4e 0%, #f8c471 100%);
    color: #fff;
  }
  .gradient-card-4 {
    background: linear-gradient(135deg, #e74c3c 0%, #f1948a 100%);
    color: #fff;
  }

  /* Subtle subtitle below big numbers */
  .card-subtitle {
    font-size: 0.85rem;
    opacity: 0.8;
  }

  /* Custom styling for upcoming and logs card */
  .custom-list-card {
    border-radius: 0.375rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  }

  .scrollable-list {
    max-height: 280px;
    overflow-y: auto;
    padding-right: 0.5rem;
  }
  .scrollable-list::-webkit-scrollbar {
    width: 6px;
  }
  .scrollable-list::-webkit-scrollbar-track {
    background: #f9f9f9;
  }
  .scrollable-list::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
  }

  /* Overdue icon size and color */
  .overdue-icon {
    font-size: 1.75rem;
    color: #fff;
  }

  /* Badge for logs or types */
  .badge-exams {
    background-color: #eaeaea;
    color: #555;
    font-size: 0.75rem;
    text-transform: uppercase;
  }

  /* Example for donut chart container */
  #examStatusChart {
    min-height: 240px;
  }
</style>
@endpush

@section('content')
<div class="container-fluid mt-4">

  <!-- Quick Stats (4 Cards) -->
  <div class="row">
    <div class="col-xl-3 col-md-6 mb-3">
      <div class="card gradient-card-1 h-100" style="border: 0!important;">
        <div class="card-body d-flex flex-column justify-content-center">
          <h5 class="mb-2 text-light">Total Exams</h5>
          <h2 class="fw-bolder mb-1 text-light">
            {{ $totalExams }}
          </h2>
          <span class="card-subtitle">All exams in the system</span>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
      <div class="card gradient-card-2 h-100"  style="border: 0!important;">
        <div class="card-body d-flex flex-column justify-content-center">
          <h5 class="mb-2 text-light">New Exams</h5>
          <h2 class="fw-bolder mb-1 text-light">
            {{ $newExams }}
          </h2>
          <span class="card-subtitle">Awaiting preparation</span>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
      <div class="card gradient-card-3 h-100" style="border: 0!important;">
        <div class="card-body d-flex flex-column justify-content-center">
          <h5 class="mb-2 text-light">Assigned Exams</h5>
          <h2 class="fw-bolder mb-1 text-light">
            {{ $pendingExams }}
          </h2>
          <span class="card-subtitle">In progress</span>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
      <div class="card gradient-card-4 h-100" style="border: 0!important;">
        <div class="card-body d-flex flex-column justify-content-center">
          <h5 class="mb-2 text-light">Completed Exams</h5>
          <h2 class="fw-bolder mb-1 text-light">
            {{ $completedExams }}
          </h2>
          <span class="card-subtitle">All done & archived</span>
        </div>
      </div>
    </div>
  </div> <!-- row -->

  <!-- Overdue / Today’s Exams / Upcoming Exams List -->
  <div class="row">
    <!-- Overdue Exams -->
    <div class="col-md-6 col-xl-3 mb-3">
      <div class="card bg-danger text-white h-100" style="border: 0!important;">
        <div class="card-body d-flex flex-column justify-content-center">
          <div class="d-flex align-items-center">
            <div class="flex-grow-1">
              <h5 class="mb-2 text-light">Overdue Exams</h5>
              <h2 class="fw-bolder mb-0 text-light">{{ $overdueExams }}</h2>
            </div>
            <div class="flex-shrink-0 ms-3 overdue-icon">
              <i class="ti ti-alert-triangle"></i>
            </div>
          </div>
          <p class="mb-0 mt-2 text-white-75">Past scheduled exam date</p>
        </div>
      </div>
    </div>

    <!-- Today’s Exams -->
    <div class="col-md-6 col-xl-3 mb-3">
      <div class="card bg-info text-white h-100" style="border: 0!important;">
        <div class="card-body d-flex flex-column justify-content-center">
          <div class="d-flex align-items-center">
            <div class="flex-grow-1">
              <h5 class="mb-2 text-light">Today’s Exams</h5>
              <h2 class="fw-bolder mb-0 text-light">{{ $todayExams->count() }}</h2>
            </div>
            <div class="flex-shrink-0 ms-3" style="font-size:1.75rem;">
              <i class="ti ti-calendar"></i>
            </div>
          </div>
          <p class="mb-0 mt-2 text-white-75">
            {{ \Carbon\Carbon::today()->format('d M Y') }}
          </p>
        </div>
      </div>
    </div>

    <!-- Upcoming Exams List -->
    <div class="col-xl-6 mb-3">
      <div class="card custom-list-card h-100" style="border: 0!important;">
        <div class="card-header">
          <h5 class="mb-0">Upcoming Exams</h5>
        </div>
        <div class="card-body scrollable-list">
          @forelse($upcomingExams as $exam)
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div>
                <h6 class="mb-1 text-primary">
                  #{{ $exam->id }} - {{ ucfirst($exam->exam_type) }}
                </h6>
                <small class="text-muted">
                  {{ optional($exam->exam_date)->format('d M Y') }}
                  / Course #{{ $exam->course_id }}
                </small>
              </div>
              <span class="badge
                @if($exam->status === 'assigned') bg-warning text-dark
                @elseif($exam->status === 'new') bg-secondary
                @elseif($exam->status === 'completed') bg-success
                @endif
              ">
                {{ ucfirst($exam->status) }}
              </span>
            </div>
            <hr class="my-1" />
          @empty
            <p class="text-muted">No upcoming exams found.</p>
          @endforelse
        </div>
      </div>
    </div>
  </div> <!-- row -->

  <!-- Chart + Logs row -->
  <div class="row">
    <!-- Recent Logs (Exam Manager only) -->
    @if($isExamManager)
    <div class="col-xl-12 mb-3">
      <div class="card custom-list-card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0">Recent Exam Updates</h5>
          <span class="text-muted" style="font-size:0.9rem;">Last 10 logs</span>
        </div>
        <div class="card-body scrollable-list">
          @forelse($recentLogs as $log)
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div>
                <p class="text-muted mb-1" style="font-size:0.8rem;">
                  <i class="ti ti-clock"></i> {{ $log->created_at->diffForHumans() }}
                </p>
                <h6 class="mb-0">{{ $log->description }}</h6>
              </div>
              <span class="badge badge-exams">{{ $log->type }}</span>
            </div>
            <hr class="my-1" />
          @empty
            <p class="text-muted mb-0">No recent logs found.</p>
          @endforelse
        </div>
      </div>
    </div>
    @endif
  </div> <!-- row -->
</div>
@endsection

@push('scripts')
<!-- Load ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Convert counts to numbers (guarding against strings)
  let newCount       = Number('{{ $newExams }}');
  let pendingCount   = Number('{{ $pendingExams }}');
  let completedCount = Number('{{ $completedExams }}');

  // ApexCharts Donut
  let donutOptions = {
    chart: {
      type: 'donut',
      height: 320
    },
    labels: ['New', 'Assigned', 'Completed'],
    series: [newCount, pendingCount, completedCount],
    colors: ['#6c757d', '#ffc107', '#28a745'], // Gray, Warning, Success
    legend: {
      position: 'bottom'
    },
    plotOptions: {
      pie: {
        donut: {
          size: '65%'
        }
      }
    },
    dataLabels: {
      dropShadow: { enabled: false }
    },
  };

  let donutChart = new ApexCharts(document.querySelector('#examStatusChart'), donutOptions);
  donutChart.render();
});
</script>
@endpush
