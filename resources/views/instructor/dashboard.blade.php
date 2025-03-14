@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('instructor.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
@endsection

@section('content')

@endsection

@push('styles')

@endpush

@push('scripts')

@endpush
