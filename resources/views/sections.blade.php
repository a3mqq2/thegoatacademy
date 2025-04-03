@extends('layouts.auth')

@section('content')
<div class="row">
   <div class="col-md-12">
      <h3 class="text-center mb-4">Please Choose Your Role</h3>
      <div class="row justify-content-center">
         @php
            $roles = [
               ['name' => 'Admin', 'icon' => 'fa-user-shield', 'route' => 'admin.dashboard'],
               ['name' => 'Instructor', 'icon' => 'fa-chalkboard-teacher', 'route' => 'instructor.dashboard'],
               ['name' => 'Exam Officer', 'icon' => 'fa-file-alt', 'route' => 'exam_officer.dashboard'],
            ];
         @endphp

         @foreach ($roles as $role)
            @if(auth()->user()->hasRole($role['name']))
               <div class="col-md-6 mb-4">
                  <a href="{{ route($role['route']) }}" class="text-decoration-none">
                     <div class="card option-card text-center border shadow-sm">
                        <div class="card-body">
                           <i class="fas {{ $role['icon'] }} fa-3x mb-3 text-primary"></i>
                           <h5 class="card-title text-dark">{{ ucfirst(str_replace('_', ' ', $role['name'])) }}</h5>
                        </div>
                     </div>
                  </a>
               </div>
            @endif
         @endforeach
      </div>
   </div>
</div>
@endsection

@push('styles')
<style>
  .option-card {
    transition: transform 0.3s ease-in-out;
    border-radius: 10px;
  }

  .option-card:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }

  .card-body i {
    color: #007bff;
  }
</style>
@endpush
