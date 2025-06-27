@extends('layouts.app')

@section('title', 'Edit Course Type')

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
    <i class="fa fa-edit"></i> Edit Course Type
  </li>
@endsection

@section('content')
<div class="container">
  <div class="card mt-3">
    <div class="card-header">
      <h4><i class="fa fa-edit"></i> Edit Course Type: {{ $courseType->name }}</h4>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.course-types.update', $courseType) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row g-3">
          <div class="col-md-6">
            <label for="name" class="form-label"><i class="fa fa-tag"></i> Name</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                   value="{{ old('name', $courseType->name) }}" placeholder="Enter course type name">
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          
          <div class="col-md-6">
            <label for="status" class="form-label"><i class="fa fa-toggle-on"></i> Status</label>
            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
              <option value="active" {{ old('status', $courseType->status) == 'active' ? 'selected' : '' }}>Active</option>
              <option value="inactive" {{ old('status', $courseType->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
       
          <div class="col-md-6">
            <label for="duration" class="form-label"><i class="fa fa-clock"></i> Classes (Count)</label>
            <input type="number" name="duration" value="{{ old('duration', $courseType->duration) }}" class="form-control">
            @error('duration')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <!-- Legacy Data Migration Notice -->
        @if(isset($currentData['has_legacy_data']) && $currentData['has_legacy_data'])
        <div class="alert alert-warning mt-4" role="alert">
          <h5 class="alert-heading"><i class="fa fa-exclamation-triangle"></i> Legacy Data Detected</h5>
          <p>This course type contains legacy skill data that should be migrated to the new system.</p>
          <hr>
          <p class="mb-0">
            <a href="{{ route('admin.course-types.migrate-legacy', $courseType) }}" class="btn btn-warning btn-sm">
              <i class="fa fa-sync"></i> Migrate Legacy Data
            </a>
          </p>
        </div>
        @endif
        
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
                        <option value="{{ $skill->id }}" 
                          {{ in_array($skill->id, old('progress_skills', $currentData['progress_skills'] ?? [])) ? 'selected' : '' }}>
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
                        <option value="{{ $skill->id }}" 
                          {{ in_array($skill->id, old('exam_skills', $currentData['exam_skills'] ?? [])) ? 'selected' : '' }}>
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

        <!-- Legacy Skills Section (إذا وجدت) -->
        @if(isset($currentData['legacy_skills']) && count($currentData['legacy_skills']) > 0)
        <div class="row mt-4">
          <div class="col-md-12">
            <div class="card border-warning">
              <div class="card-header bg-warning text-dark">
                <h5><i class="fa fa-exclamation-triangle"></i> Legacy Skills (Need Migration)</h5>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th>Skill</th>
                        <th>Progress Test Max</th>
                        <th>Mid Max</th>
                        <th>Final Max</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($currentData['legacy_grades'] as $skillId => $grades)
                        @php
                          $skill = $skills->find($skillId);
                        @endphp
                        @if($skill)
                        <tr>
                          <td>{{ $skill->name }}</td>
                          <td>{{ $grades['progress_test_max'] ?? 'N/A' }}</td>
                          <td>{{ $grades['mid_max'] ?? 'N/A' }}</td>
                          <td>{{ $grades['final_max'] ?? 'N/A' }}</td>
                        </tr>
                        @endif
                      @endforeach
                    </tbody>
                  </table>
                </div>
                <div class="alert alert-info">
                  <small><i class="fa fa-info-circle"></i> These skills use the old system. Please migrate them to the new separated system above.</small>
                </div>
              </div>
            </div>
          </div>
        </div>
        @endif
        
        <div class="mt-4 d-flex justify-content-end">
          <a href="{{ route('admin.course-types.index') }}" class="btn btn-outline-secondary me-2">
            <i class="fa fa-arrow-left"></i> Cancel
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Update Course Type
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
    .alert-heading {
      margin-bottom: 0.5rem;
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

      // البيانات الحالية من الخادم
      const currentData = @json($currentData ?? []);
      
      // Function to update Progress Test Skills table
      function updateProgressSkillsTable() {
        var data = $('#progress_skills').select2('data');
        var tbody = $('#progress-skills-tbody');
        tbody.empty();
        
        data.forEach(function(skill) {
          var skillId = skill.id;
          var skillName = skill.text;
          var currentGrade = currentData.progress_grades && currentData.progress_grades[skillId] 
                            ? currentData.progress_grades[skillId].max_grade : '';
          
          var row = '<tr data-skill-id="' + skillId + '">';
          row += '<td>' + skillName + '<input type="hidden" name="progress_grades[' + skillId + '][skill_id]" value="' + skillId + '"></td>';
          row += '<td><input type="number" step="0.01" name="progress_grades[' + skillId + '][max_grade]" class="form-control" placeholder="Enter max grade" value="' + currentGrade + '" required></td>';
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
          var currentMidMax = currentData.exam_grades && currentData.exam_grades[skillId] 
                             ? currentData.exam_grades[skillId].mid_max : '';
          var currentFinalMax = currentData.exam_grades && currentData.exam_grades[skillId] 
                               ? currentData.exam_grades[skillId].final_max : '';
          
          var row = '<tr data-skill-id="' + skillId + '">';
          row += '<td>' + skillName + '<input type="hidden" name="exam_grades[' + skillId + '][skill_id]" value="' + skillId + '"></td>';
          row += '<td><input type="number" step="0.01" name="exam_grades[' + skillId + '][mid_max]" class="form-control" placeholder="Enter mid max grade" value="' + currentMidMax + '" required></td>';
          row += '<td><input type="number" step="0.01" name="exam_grades[' + skillId + '][final_max]" class="form-control" placeholder="Enter final max grade" value="' + currentFinalMax + '" required></td>';
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

      // Initialize tables on page load with current data
      updateProgressSkillsTable();
      updateExamSkillsTable();
    });
  </script>
@endpush