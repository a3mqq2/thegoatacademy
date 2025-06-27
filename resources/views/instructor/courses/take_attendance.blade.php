@extends('layouts.app')

@section('title', 'Take Attendance')

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header">
      <h4><i class="fa fa-check-square"></i> Take Attendance</h4>
    </div>
    <div class="card-body" id="app">
      <attendance :course-id="{{ $course->id }}" :date="'{{ now() }}'" :schedule-id="{{$schedule}}"
        :is-admin="{{get_area_name() == 'admin'}}" 
        ></attendance>
    </div>
  </div>
</div>
@endsection
