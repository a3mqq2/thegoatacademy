@extends('layouts.app')
@section('content')

<div class="row mt-3">
  <div class="col-lg-3 col-md-6">
      <a href="{{route('admin.students.index')}}">
         <div class="card">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col-8">
                  <h3 class="mb-1">{{$students}}</h3>
                  <p class="text-muted mb-0">All students</p>
                </div>
                <div class="col-4 text-end">
                  <i class="ti ti-user text-secondary f-36"></i>
                </div>
              </div>
            </div>
          </div>
      </a>
  </div>
  <div class="col-md-6 col-xl-3">
    <a href="{{route('admin.courses.index', ['status' => 'upcoming'])}}">
      <div class="card social-widget-card bg-primary">
        <div class="card-body">
          <h3 class="text-white m-0">{{$upcoming_courses}}</h3>
          <span class="m-t-10">Upcoming Courses</span>
          <i class="fas fa-users"></i>
        </div>
      </div>
    </a>
  </div>
  <div class="col-md-6 col-xl-3">
    <a href="{{route('admin.courses.index',['status' => 'ongoing'])}}">
      <div class="card social-widget-card bg-warning">
        <div class="card-body">
          <h3 class="text-white m-0">{{$ongoing_courses}}</h3>
          <span class="m-t-10">Ongoing Courses</span>
          <i class="fas fa-users"></i>
        </div>
      </div>
    </a>
  </div>
  <div class="col-md-6 col-xl-3">
    <a href="{{route('admin.courses.index',['status' => 'completed'])}}">
      <div class="card social-widget-card bg-danger">
        <div class="card-body">
          <h3 class="text-white m-0">{{$completed_courses}}</h3>
          <span class="m-t-10">Completed Courses</span>
          <i class="fas fa-users"></i>
        </div>
      </div>
    </a>
  </div>
</div>



@push('scripts')
<script>
  // Pass data from Laravel to JavaScript
  var studentData = {
      today: {{ $todayCount }},
      weekly: {{ $weeklyCount }},
      monthly: {{ $monthlyCount }},
      chartData: {!! json_encode($chartData) !!} 
  };
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Define the student chart data
    let options3 = {
        series: [{
            data: studentData.chartData
        }],
        chart: {
            type: "rangeBar",
            height: 80,
            sparkline: { enabled: true },
            toolbar: { show: false }
        },
        colors: ["#E58A00"],
        plotOptions: {
            bar: {
                columnWidth: "30%",
                borderRadius: 5,
                horizontal: false
            }
        },
        yaxis: {
            tickAmount: 2,
            min: 0,
            max: Math.max(10, studentData.monthly)
        },
        grid: {
            show: false,
            padding: { top: 0, right: 0, bottom: 0, left: 0 }
        },
        xaxis: {
            labels: { show: false },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        dataLabels: { enabled: false }
    };

    var chart = new ApexCharts(document.querySelector("#new-users-graph"), options3);
    chart.render();
});
</script>
@endpush

@endsection
