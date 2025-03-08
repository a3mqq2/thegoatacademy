@extends('layouts.app')

@section('title', 'Quality Settings')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
  <li class="breadcrumb-item active">
    <i class="fa fa-cogs"></i> Quality Settings
  </li>
@endsection

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header bg-light text-white">
      <h4 class="text-primary"><i class="fa fa-cogs"></i> Quality Settings</h4>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.quality-settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <table class="table table-bordered text-center">
          <thead class="table-light">
            <tr>
              <th>Category</th>
              <th class="bg-danger text-white">Red Starts From</th>
              <th class="bg-warning text-dark">Yellow Starts From</th>
              <th class="bg-success text-white">Green Starts From</th>
            </tr>
          </thead>
          <tbody>
            @foreach($settings as $setting)
              <tr>
                <td class="text-start fw-bold">
                  <i class="fa fa-check-circle text-primary"></i> {{ ucfirst(str_replace('_', ' ', $setting->type)) }}
                </td>
                <td class="bg-danger-light">
                  <input type="number" class="form-control text-center border-danger fw-bold" name="settings[{{ $setting->id }}][red_threshold]" value="{{ $setting->red_threshold }}">
                </td>
                <td class="bg-warning-light">
                  <input type="number" class="form-control text-center border-warning fw-bold" name="settings[{{ $setting->id }}][yellow_threshold]" value="{{ $setting->yellow_threshold }}">
                </td>
                <td class="bg-success-light">
                  <input type="number" class="form-control text-center border-success fw-bold" name="settings[{{ $setting->id }}][green_threshold]" value="{{ $setting->green_threshold }}">
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>

        <div class="mt-4 text-end">
          <button type="submit" class="btn btn-success px-4">
            <i class="fa fa-save"></i> Save Settings
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  .bg-danger-light { background-color: rgba(220, 53, 69, 0.15) !important; }
  .bg-warning-light { background-color: rgba(255, 193, 7, 0.15) !important; }
  .bg-success-light { background-color: rgba(40, 167, 69, 0.15) !important; }
</style>
@endpush
