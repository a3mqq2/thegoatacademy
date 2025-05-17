@extends('layouts.app')

@section('title', 'Create Course')

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header">
      <h4><i class="fa fa-plus"></i> Create Course</h4>
    </div>
    <div class="card-body" id="app">
      <create-course></create-course>
    </div>
  </div>
</div>
@endsection
