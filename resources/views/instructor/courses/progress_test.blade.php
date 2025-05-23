@extends('layouts.app')

@section('title', isset($progressTest) ? 'Edit Progress Test' : 'New Progress Test')

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header">
      <h4><i class="fa fa-check-square"></i>
        {{ isset($progressTest) ? 'Edit Progress Test' : 'New Progress Test' }}
      </h4>

      <a href="{{route("instructor.courses.progress_tests.print", $progressTest)}}" class="btn btn-danger text-light mt-3"> Download Results <i class="fa fa-print"></i> </a>

    </div>
    <div class="card-body" id="app">
      <progress-test 
        @if(isset($progressTest))
          :progress-test-id="{{ $progressTest->id }}"
        @endif
      ></progress-test>
    </div>
  </div>
</div>
@endsection
