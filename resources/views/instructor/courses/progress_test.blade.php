@extends('layouts.app')

@section('title', 'New Progress Test')

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header">
      <h4><i class="fa fa-check-square"></i> New Progress Test </h4>
    </div>
    <div class="card-body">
      <progress-test :course-id="{{ $course->id }}" ></progress-test>
    </div>
  </div>
</div>
@endsection
