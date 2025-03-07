@if ($errors->any())
@foreach ($errors->all() as $errorMessage)
<div class="alert alert-danger" role="alert">
    <small><i class="mx-2 fa fa-exclamation-circle mr-2 "></i> {{$errorMessage}} </small>
</div>
@endforeach
@endif


@if (session()->has('error'))
<div class="alert alert-danger" role="alert">
    <small><i class="mx-2 fa fa-exclamation-circle mr-2 "></i> {{session()->get('error')}} </small>
</div>
@endif

@if (session()->has('success'))
<div class="alert alert-success" role="alert">
    <small><i class="mx-2 fa fa-check-circle mr-2 "></i> {{session()->get('success')}} </small>
</div>
@endif