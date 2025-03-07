@extends('layouts.auth')

@section('content')
<div class="row">
   <form action="{{ route('do_login') }}" method="POST">
      @csrf
      @method('POST')
      <div class="col-md-12">
         <div class="text-center">
           <h3 class="text-center mb-3">Welcome Back</h3>
           <p class="mb-4">Please Login Before Continue</p>
         </div>
         <div class="row my-4">
         
           <div class="col-12">
             <div class="mb-3">
               <label class="form-label">Email</label>
               <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email" value="{{ old('email') }}" />
               @error('email')
                   <div class="invalid-feedback">{{ $message }}</div>
               @enderror
             </div>
           </div>

           <div class="col-12">
             <div class="mb-3">
               <label class="form-label">Password</label>
               <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password" />
               @error('password')
                   <div class="invalid-feedback">{{ $message }}</div>
               @enderror
             </div>
           </div>

         </div>
         <div class="row g-3">
           <div class="col-sm-12">
             <div class="d-grid">
               <button class="btn btn-primary">Login</button>
             </div>
           </div>
         </div>
      </div>
   </form>
</div>
@endsection
