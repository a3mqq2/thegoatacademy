@extends('layouts.app')

@section('title', 'Courses Table')


@php
  use Carbon\Carbon;
  $today      = Carbon::today();
  $startWeek  = $today->copy()->startOfWeek(Carbon::SATURDAY);
  $endWeek    = $today->copy()->endOfWeek(Carbon::FRIDAY);
  $afterOne   = $today->copy()->addDay()->toDateString();
  $afterTwo   = $today->copy()->addDays(2)->toDateString();
  $schedule   = request('schedule', '');
@endphp

@section('content')
<div class="mt-4">
  <div class="card shadow-sm">
    <div class="card-body">

      {{-- Quick Filters --}}
      <div class="mb-3">
        <div class="btn-group mb-2">
          <a href="{{ route(get_area_name().'.courses.index', array_merge(request()->all(), ['schedule'=>'daily'])) }}" class="btn btn-outline-primary">This Day</a>
          <a href="{{ route(get_area_name().'.courses.index', array_merge(request()->all(), ['schedule'=>'weekly'])) }}" class="btn btn-outline-primary">This Week</a>
          <a href="{{ route(get_area_name().'.courses.index', array_merge(request()->all(), ['schedule'=>'afterTwoDays'])) }}" class="btn btn-outline-primary">After Two Days</a>
          <a href="{{ route(get_area_name().'.courses.index', array_merge(request()->all(), ['schedule'=>'afterADay'])) }}" class="btn btn-outline-primary">After a Day</a>
        </div>
        <a href="{{ route(get_area_name().'.courses.index') }}" class="btn btn-outline-secondary mb-2">Remove Filter</a>
        @if (request('schedule'))
        <a href="{{ route(get_area_name().'.courses.index', ['print' => 1, 'schedule' => request('schedule')]) }}" class="btn btn-outline-danger mb-2">Print <i class="fa fa-print"></i></a>
      @endif
    </div>
      </div>

      {{-- Advanced Filters --}}
      <div class="card mb-4">
        <div class="card-header">Advanced Filters</div>
        <div class="card-body">
          <form method="GET" action="{{ route(get_area_name().'.courses.index') }}">
            <div class="row gx-2 gy-2">
              <div class="col-md-3">
                <label>Instructor</label>
                <select name="instructor_id" class="form-control">
                  <option value="">All</option>
                  @foreach($instructors as $i)
                    <option value="{{ $i->id }}" @selected(request('instructor_id')==$i->id)>{{ $i->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <label>Examiner</label>
                <select name="examiner_id" class="form-control">
                  <option value="">All</option>
                  @foreach($examiners as $e)
                    <option value="{{ $e->id }}" @selected(request('examiner_id')==$e->id)>{{ $e->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <label>Exam Date</label>
                <input type="date" name="exam_date" id="exam_date" class="form-control" value="{{ request('exam_date') }}" autocomplete="off">
              </div>
              <div class="col-md-3">
                <label>Course Type</label>
                <select name="course_type_id" class="form-control">
                  <option value="">All</option>
                  @foreach($courseTypes as $ct)
                    <option value="{{ $ct->id }}" @selected(request('course_type_id')==$ct->id)>{{ $ct->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <button class="btn btn-primary mt-2">Apply Filters</button>
          </form>
        </div>
      </div>

      {{-- Courses Table --}}
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>TIME</th>
              <th>DAYS</th>
              <th>PRE TEST DATE</th>
              <th>MID TEST DATE</th>
              <th>FINAL TEST DATE</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($courses as $course)
              @php
                [$start,$end] = explode(' - ',$course->time);
                $fs = \Carbon\Carbon::createFromFormat('H:i',$start)->format('h:i A');
                $fe = \Carbon\Carbon::createFromFormat('H:i',$end)->format('h:i A');
                $dates = [
                  'pre'  => $course->pre_test_date,
                  'mid'  => $course->mid_exam_date,
                  'final'=> $course->final_exam_date,
                ];
                $classes = [];
                foreach($dates as $key=>$d) {
                  $cls = '';
                  if ($schedule==='daily' && $d && $d == $today->toDateString()) {
                    $cls = 'table-danger';
                  }
                  if ($schedule==='weekly' && $d && \Carbon\Carbon::parse($d)->between($startWeek,$endWeek)) {
                    $cls = 'table-danger';
                  }
                  if ($schedule==='afterTwoDays' && $d && $d == $afterTwo) {
                    $cls = 'table-danger';
                  }
                  if ($schedule==='afterADay' && $d && $d == $afterOne) {
                    $cls = 'table-danger';
                  }
                  $classes[$key] = $cls;
                }
              @endphp
              <tr>
                <td>{{ $course->id }}</td>
                <td>{{ $fs }} - {{ $fe }}</td>
                <td>{{ $course->days }}</td>
                <td class="{{ $classes['pre'] }}">{{ $course->pre_test_date ?? '-' }}</td>
                <td class="{{ $classes['mid'] }}">{{ $course->mid_exam_date ?? '-' }}</td>
                <td class="{{ $classes['final'] }}">{{ $course->final_exam_date ?? '-' }}</td>
                <td>
                  <a href="{{ route(get_area_name().'.courses.show',$course->id) }}" class="btn btn-info btn-sm">View</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
        {{ $courses->withQueryString()->links() }}
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

@endpush
