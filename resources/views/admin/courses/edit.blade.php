@extends('layouts.app')

@section('title', 'Edit Course')

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header">
      <h4><i class="fa fa-plus"></i> Edit Course</h4>
    </div>
    <div class="card-body" id="app">
      <edit-course id="{{$course->id}}" ></edit-course>
    </div>
  </div>
</div>
@endsection
