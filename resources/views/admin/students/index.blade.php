@extends('layouts.app')

@section('title', 'Students List')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
  <li class="breadcrumb-item active">
    <i class="fa fa-users"></i> Students
  </li>
@endsection

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4><i class="fa fa-users"></i> Students List</h4>
      <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i> Add Student
      </a>
    </div>
    <div class="card-body">
      <form method="GET" action="{{ route('admin.students.index') }}" class="mb-3">
        <div class="row g-2">
          <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Search students" value="{{ request('search') }}">
          </div>
          <div class="col-md-2">
            <select name="gender" class="form-select">
              <option value="">All Genders</option>
              <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
              <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
            </select>
          </div>
          <div class="col-md-2">
            <input type="text" name="city" class="form-control" placeholder="City" value="{{ request('city') }}">
          </div>
          <div class="col-md-2">
            <input type="number" name="age" class="form-control" placeholder="Age" value="{{ request('age') }}">
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-secondary">
              <i class="fa fa-filter"></i> Filter
            </button>
            <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">
              <i class="fa fa-redo"></i> Reset
            </a>
          </div>
        </div>
      </form>
      
      <div class="table-responsive">
        <table class="table table-striped table-bordered">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th> Image </th>
              <th>Phone</th>
              <th>City</th>
              <th>Age</th>
              <th>Specialization</th>
              <th>Gender</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($students as $student)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{$student->name}}</td>
                <td>
                  <img src="{{Storage::url($student->avatar)}}" style="width:50px;" alt="">
                </td>
                <td>{{ $student->phone }}</td>
                <td>{{ $student->city }}</td>
                <td>{{ $student->age }}</td>
                <td>{{ $student->specialization }}</td>
                <td>{{ ucfirst($student->gender) }}</td>
                <td>
                  <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-sm btn-info">
                    <i class="fa fa-eye"></i>
                  </a>
  
                  <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-sm btn-warning">
                    <i class="fa fa-edit"></i>
                  </a>
                  <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $student->id }}">
                    <i class="fa fa-trash"></i>
                  </button>
  
  
                  {{-- modal for delete --}}
                  <div class="modal fade text-start" id="deleteModal-{{ $student->id }}" tabindex="-1" aria-labelledby="deleteModalLabel-{{ $student->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="deleteModalLabel-{{ $student->id }}">
                            <i class="fa fa-exclamation-triangle text-danger"></i> Warning
                          </h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                          <p>Are you sure you want to delete this student?</p>
                          <form method="POST" action="{{ route('admin.students.destroy', $student->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                              <i class="fa fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-danger">
                              <i class="fa fa-trash"></i> Delete
                            </button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
  
            @empty
              <tr>
                <td colspan="8" class="text-center">No students found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $students->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
