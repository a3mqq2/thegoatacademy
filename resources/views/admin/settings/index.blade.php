{{-- resources/views/admin/users/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Settings')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
  <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></li>
@endsection

@section('content')
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <!-- Example form to update settings -->
        <form action="{{ route('admin.settings.update') }}" method="POST">
          @csrf
          @method('PUT')

          <div class="card">
            <div class="card-header">
              <h4>Settings</h4>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>Key</th>
                      <th>Value</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($settings as $setting)
                      <tr>
                        <td>{{ $setting->key }}</td>
                        <td>
                          <!-- Give each textarea the class 'editor' so Summernote can replace it -->
                          <textarea
                            name="settings[{{ $setting->key }}]"
                            class="form-control editor"
                            cols="30"
                            rows="10"
                          >{{ $setting->value }}</textarea>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
            <div class="card-footer text-end">
              <button type="submit" class="btn btn-primary">Save</button>
            </div>
          </div>
        </form>
        <!-- End form -->
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <!-- If you don't already have jQuery and Bootstrap, load them here.
       (Otherwise, ensure they're included somewhere before Summernote.) -->

  <!-- jQuery (required for Summernote) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"
          integrity="sha256-/xUj+3OJ+...q8Z8nEGS...goR6AiZk" 
          crossorigin="anonymous"></script>

  <!-- Bootstrap JS (Summernote depends on Bootstrap) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
          integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+...9jOWxB9EiGd" 
          crossorigin="anonymous"></script>

  <!-- Summernote CSS/JS -->
  <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote.min.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Turn each <textarea class="editor"> into a Summernote editor
      $('.editor').summernote({
        height: 250,           // Height of the editor in px
        placeholder: 'Type here...',
        toolbar: [
          // Customize your toolbar (examples below)
          ['style', ['style']],
          ['font', ['bold', 'italic', 'underline', 'clear']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['insert', ['link', 'picture', 'video']],
          ['view', ['fullscreen', 'codeview', 'help']]
        ]
      });
    });
  </script>
@endpush
