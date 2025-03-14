@extends('layouts.app')

@section('title', 'Skills')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
  <li class="breadcrumb-item active">
    <i class="fa fa-cogs"></i> Skills
  </li>
@endsection

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4><i class="fa fa-cogs"></i> Skills</h4>
      <a href="{{ route('admin.skills.create') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i> Add Skill
      </a>
    </div>
    <div class="card-body">
      <form method="GET" action="{{ route('admin.skills.index') }}" class="mb-4">
        <div class="input-group">
          <input type="text" name="name" class="form-control" placeholder="Search by name" value="{{ request('name') }}">
          <button class="btn btn-outline-secondary" type="submit"><i class="fa fa-search"></i> Search</button>
        </div>
      </form>

      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($skills as $skill)
            <tr>
              <td>{{ $skill->id }}</td>
              <td>{{ $skill->name }}</td>
              <td>{{ $skill->created_at->format('Y-m-d') }}</td>
              <td>
                <a href="{{ route('admin.skills.edit', $skill->id) }}" class="btn btn-sm btn-warning">
                  <i class="fa fa-edit"></i> Edit
                </a>
                <form action="{{ route('admin.skills.destroy', $skill->id) }}" method="POST" style="display:inline-block;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger">
                    <i class="fa fa-trash"></i> Delete
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center">No skills found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>

      <div class="mt-4">
        {{ $skills->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
