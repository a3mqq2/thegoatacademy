@extends('layouts.app')
@section('title', 'Courses Table')

@push('styles')
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <style>
    @media print {
      body { font-size: 12pt; color: #000; }
      table, .table-bordered { border: 1px solid #000 !important; }
      table th, table td { border: 1px solid #000 !important; padding: 8px !important; }
    }
  </style>
@endpush

@section('content')
<div class="mt-4">
  <div class="card shadow-sm">
    <div class="card-body">

      <!-- Quick Filtering Buttons -->
      <div class="mb-3">
        <div class="btn-group mb-2" role="group" aria-label="Exam Status Filters">
          <a href="{{ route(get_area_name().'.courses.index', array_merge(request()->all(), ['status' => 'new'])) }}" class="btn btn-outline-primary">New</a>
          <a href="{{ route(get_area_name().'.courses.index', array_merge(request()->all(), ['status' => 'pending'])) }}" class="btn btn-outline-primary">Pending</a>
          <a href="{{ route(get_area_name().'.courses.index', array_merge(request()->all(), ['status' => 'completed'])) }}" class="btn btn-outline-primary">Completed</a>
          <a href="{{ route(get_area_name().'.courses.index', array_merge(request()->all(), ['status' => 'overdue'])) }}" class="btn btn-outline-primary">Overdue</a>
        </div>
        <div class="btn-group mb-2" role="group" aria-label="Schedule Filters">
          <a href="{{ route(get_area_name().'.courses.index', array_merge(request()->all(), ['schedule' => 'daily'])) }}" class="btn btn-outline-primary">This Day</a>
          <a href="{{ route(get_area_name().'.courses.index', array_merge(request()->all(), ['schedule' => 'weekly'])) }}" class="btn btn-outline-primary">This Week</a>
          <a href="{{ route(get_area_name().'.courses.index', array_merge(request()->all(), ['schedule' => 'afterTwoDays'])) }}" class="btn btn-outline-primary">After Two Days</a>
        </div>
        <a href="{{ route(get_area_name().'.courses.index') }}" class="btn btn-outline-secondary mb-2">Remove Filter</a>

        @if (request('schedule'))
          <a href="{{ route(get_area_name().'.courses.index', ['print' => 1, 'schedule' => request('schedule')]) }}" class="btn btn-outline-danger mb-2">Print <i class="fa fa-print"></i></a>
        @endif
      </div>

      <!-- Advanced Filtering Card -->
      <div class="card mb-4">
        <div class="card-header">
          Advanced Filters
        </div>
        <div class="card-body">
          <form action="{{ route(get_area_name().'.courses.index') }}" method="GET" class="form-horizontal">
            <div class="row">
              <div class="col-md-3 mb-2">
                <label for="instructor_id" class="form-label">Instructor</label>
                <select name="instructor_id" id="instructor_id" class="form-control">
                  <option value="">All</option>
                  @foreach($instructors as $instructor)
                      <option value="{{ $instructor->id }}" @if(request('instructor_id') == $instructor->id) selected @endif>
                          {{ $instructor->name }}
                      </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3 mb-2">
                <label for="examiner_id" class="form-label">Examiner</label>
                <select name="examiner_id" id="examiner_id" class="form-control">
                  <option value="">All</option>
                  @foreach($examiners as $examiner)
                      <option value="{{ $examiner->id }}" @if(request('examiner_id') == $examiner->id) selected @endif>
                          {{ $examiner->name }}
                      </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3 mb-2">
                <label for="exam_date" class="form-label">Exam Date</label>
                <input type="text" name="exam_date" id="exam_date" class="form-control" value="{{ request('exam_date') }}" autocomplete="off">
              </div>
              <div class="col-md-3 mb-2">
                <label for="course_type_id" class="form-label">Course Type</label>
                <select name="course_type_id" id="course_type_id" class="form-control">
                  <option value="">All</option>
                  @foreach($courseTypes as $courseType)
                      <option value="{{ $courseType->id }}" @if(request('course_type_id') == $courseType->id) selected @endif>
                          {{ $courseType->name }}
                      </option>
                  @endforeach
                </select>
              </div>
            </div>
            <button type="submit" class="btn btn-primary mt-2">Apply Filters</button>
          </form>
        </div>
      </div>

      <!-- Courses Table -->
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
              <td>
                <a href="{{ route(get_area_name().'.courses.show', $course->id) }}" class="btn btn-info btn-sm">View</a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
  $(document).ready(function(){
    $("#exam_date").datepicker({
      dateFormat: "yy-mm-dd"
    });
  });
</script>
@endpush
