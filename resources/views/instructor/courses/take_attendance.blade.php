@extends('layouts.app')

@section('title', 'Take Attendance')

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header">
      <h4><i class="fa fa-check-square"></i> Take Attendance</h4>
    </div>
    <div class="card-body">
      <attendance :course-id="{{ $course->id }}" :date="'{{ now() }}'" :schedule-id="{{$schedule}}"></attendance>
    </div>
  </div>
</div>
@endsection
