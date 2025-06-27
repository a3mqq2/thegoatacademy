@extends('layouts.app')

@section('title', 'Create Course Type')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
  <li class="breadcrumb-item">
    <a href="{{ route('admin.course-types.index') }}">
      <i class="fa fa-book"></i> Course Types
    </a>
  </li>
  <li class="breadcrumb-item active">
    <i class="fa fa-plus"></i> Create Course Type
  </li>
@endsection

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header">
      <h4><i class="fa fa-plus"></i> Create Course Type</h4>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.course-types.store') }}" method="POST">
        @csrf
        <div class="row g-3">
          <div class="col-md-6">
            <label for="name" class="form-label"><i class="fa fa-tag"></i> Name</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                   value="{{ old('name') }}" placeholder="Enter course type name">
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          
          <div class="col-md-6">
            <label for="status" class="form-label"><i class="fa fa-toggle-on"></i> Status</label>
            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
              <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
              <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
       
          <div class="col-md-6">
            <label for="duration" class="form-label"><i class="fa fa-clock"></i> Classes (Count)</label>
            <input type="number" name="duration" value="{{ old('duration') }}" class="form-control">
            @error('duration')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        
        <!-- Progress Test Skills Section -->
        <div class="row mt-4">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header bg-info text-white">
                <h5><i class="fa fa-chart-line"></i> Progress Test Skills</h5>
              </div>
              <div class="card-body">
                <div class="row mb-3">
                  <div class="col-md-12">
                    <label for="progress_skills" class="form-label"><i class="fa fa-code"></i> Select Progress Test Skills</label>
                    <select name="progress_skills[]" id="progress_skills" class="form-select @error('progress_skills') is-invalid @enderror" multiple>
                      @foreach($skills as $skill)
                        <option value="{{ $skill->id }}">
                          {{ $skill->name }}
                        </option>
                      @endforeach
                    </select>
                    @error('progress_skills')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                
                <!-- Progress Test Skills Grades Table -->
                <table class="table table-bordered" id="progress-skills-table">
                  <thead>
                    <tr>
                      <th>Skill</th>
                      <th>Max Grade</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody id="progress-skills-tbody">
                    {{-- سيتم تعبئة الصفوف تلقائيًا باستخدام JavaScript --}}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Mid & Final Skills Section -->
        <div class="row mt-4">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header bg-success text-white">
                <h5><i class="fa fa-graduation-cap"></i> Mid & Final Exam Skills</h5>
              </div>
              <div class="card-body">
                <div class="row mb-3">
                  <div class="col-md-12">
                    <label for="exam_skills" class="form-label"><i class="fa fa-code"></i> Select Mid & Final Skills</label>
                    <select name="exam_skills[]" id="exam_skills" class="form-select @error('exam_skills') is-invalid @enderror" multiple>
                      @foreach($skills as $skill)
                        <option value="{{ $skill->id }}">
                          {{ $skill->name }}
                        </option>
                      @endforeach
                    </select>
                    @error('exam_skills')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                
                <!-- Mid & Final Skills Grades Table -->
                <table class="table table-bordered" id="exam-skills-table">
                  <thead>
                    <tr>
                      <th>Skill</th>
                      <th>Mid Max Grade</th>
                      <th>Final Max Grade</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody id="exam-skills-tbody">
                    {{-- سيتم تعبئة الصفوف تلقائيًا باستخدام JavaScript --}}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        
        <div class="mt-4 d-flex justify-content-end">
          <a href="{{ route('admin.course-types.index') }}" class="btn btn-outline-secondary me-2">
            <i class="fa fa-arrow-left"></i> Cancel
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Save Course Type
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('styles')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    .select2-container {
      width: 100% !important;
    }
    .card-header h5 {
      margin: 0;
    }
  </style>
@endpush

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    $(document).ready(function() {
      // Initialize Select2
      $('#progress_skills').select2({
        placeholder: 'Select progress test skills',
        allowClear: true
      });
      
      $('#exam_skills').select2({
        placeholder: 'Select mid & final exam skills',
        allowClear: true
      });

      // Function to update Progress Test Skills table
      function updateProgressSkillsTable() {
        var data = $('#progress_skills').select2('data');
        var tbody = $('#progress-skills-tbody');
        tbody.empty();
        
        data.forEach(function(skill) {
          var skillId = skill.id;
          var skillName = skill.text;
          var row = '<tr data-skill-id="' + skillId + '">';
          row += '<td>' + skillName + '<input type="hidden" name="progress_grades[' + skillId + '][skill_id]" value="' + skillId + '"></td>';
          row += '<td><input type="number" step="0.01" name="progress_grades[' + skillId + '][max_grade]" class="form-control" placeholder="Enter max grade" required></td>';
          row += '<td><button type="button" class="btn btn-sm btn-outline-danger remove-progress-skill" data-skill-id="' + skillId + '"><i class="fa fa-trash"></i></button></td>';
          row += '</tr>';
          tbody.append(row);
        });
      }

      // Function to update Mid & Final Skills table
      function updateExamSkillsTable() {
        var data = $('#exam_skills').select2('data');
        var tbody = $('#exam-skills-tbody');
        tbody.empty();
        
        data.forEach(function(skill) {
          var skillId = skill.id;
          var skillName = skill.text;
          var row = '<tr data-skill-id="' + skillId + '">';
          row += '<td>' + skillName + '<input type="hidden" name="exam_grades[' + skillId + '][skill_id]" value="' + skillId + '"></td>';
          row += '<td><input type="number" step="0.01" name="exam_grades[' + skillId + '][mid_max]" class="form-control" placeholder="Enter mid max grade" required></td>';
          row += '<td><input type="number" step="0.01" name="exam_grades[' + skillId + '][final_max]" class="form-control" placeholder="Enter final max grade" required></td>';
          row += '<td><button type="button" class="btn btn-sm btn-outline-danger remove-exam-skill" data-skill-id="' + skillId + '"><i class="fa fa-trash"></i></button></td>';
          row += '</tr>';
          tbody.append(row);
        });
      }

      // Update tables when selections change
      $('#progress_skills').on('change', function() {
        updateProgressSkillsTable();
      });
      
      $('#exam_skills').on('change', function() {
        updateExamSkillsTable();
      });

      // Remove skill from Progress Test table
      $(document).on('click', '.remove-progress-skill', function() {
        var skillId = $(this).data('skill-id');
        var currentValues = $('#progress_skills').val() || [];
        var newValues = currentValues.filter(function(value) {
          return value != skillId;
        });
        $('#progress_skills').val(newValues).trigger('change');
      });

      // Remove skill from Exam table
      $(document).on('click', '.remove-exam-skill', function() {
        var skillId = $(this).data('skill-id');
        var currentValues = $('#exam_skills').val() || [];
        var newValues = currentValues.filter(function(value) {
          return value != skillId;
        });
        $('#exam_skills').val(newValues).trigger('change');
      });

      // Initialize tables on page load
      updateProgressSkillsTable();
      updateExamSkillsTable();
    });
  </script>
@endpush