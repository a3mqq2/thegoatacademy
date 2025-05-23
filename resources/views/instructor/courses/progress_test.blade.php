@extends('layouts.app')

@section('title', isset($progressTest) ? 'Edit Progress Test' : 'New Progress Test')

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header">
      <h4><i class="fa fa-check-square"></i>
        {{ isset($progressTest) ? 'Edit Progress Test' : 'New Progress Test' }}
      </h4>


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
