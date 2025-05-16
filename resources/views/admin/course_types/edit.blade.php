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
      <h4><i class="fa fa-edit"></i> Edit Course Type</h4>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.course-types.update', $courseType->id) }}" method="POST">
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
          
          <!-- Multiselect for Skills using Select2 -->
          <div class="col-md-6">
            <label for="skills" class="form-label"><i class="fa fa-code"></i> Skills</label>
            <select name="skills[]" id="skills" class="form-select @error('skills') is-invalid @enderror" multiple>
              @foreach($skills as $skill)
                <option value="{{ $skill->id }}" {{ collect(old('skills', $courseType->skills->pluck('id')->toArray()))->contains($skill->id) ? 'selected' : '' }}>
                  {{ $skill->name }}
                </option>
              @endforeach
            </select>
            @error('skills')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        
        <!-- Table for editing skill grades -->
        <div class="row mt-4">
          <div class="col-md-12">
            <h5>Skill Grades Settings</h5>
            <table class="table table-bordered" id="skills-grades-table">
              <thead>
                <tr>
                  <th>Skill</th>
                  <th>Progress test Max Grade</th>
                  <th>Mid Max Grade</th>
                  <th>Final Max Grade</th>
                </tr>
              </thead>
              <tbody id="skills-grades-tbody">
                {{-- Rows will be inserted dynamically using JavaScript --}}
              </tbody>
            </table>
          </div>
        </div>
        
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
@endpush

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#skills').select2({
        placeholder: 'Select skills',
        allowClear: true
      });

      // Preload existing pivot grade values into a JavaScript object.
      let initialSkillGrades = @json(
        $courseType->skills->mapWithKeys(function ($skill) {
            return [
                $skill->id => [
                    'progress_test_max' => $skill->pivot->progress_test_max,
                    'mid_max' => $skill->pivot->mid_max,
                    'final_max' => $skill->pivot->final_max
                ]
            ];
        })
      );

      // Function to update the skills grades table based on selected skills and prefill values if available.
      function updateSkillsTable() {
        let data = $('#skills').select2('data');
        let tbody = $('#skills-grades-tbody');
        tbody.empty();
        
        data.forEach(function(skill) {
          let optionId = skill.id;
          let skillName = skill.text;
          let progressTestMax = initialSkillGrades[optionId] ? initialSkillGrades[optionId].progress_test_max : '';
          let midMax = initialSkillGrades[optionId] ? initialSkillGrades[optionId].mid_max : '';
          let finalMax = initialSkillGrades[optionId] ? initialSkillGrades[optionId].final_max : '';
          
          let row = '<tr>';
          // Display the skill name and include a hidden input for the skill ID.
          row += '<td>' + skillName + '<input type="hidden" name="skill_grades['+ optionId +'][skill_id]" value="'+ optionId +'"></td>';
          row += '<td><input type="number" step="any" name="skill_grades['+ optionId +'][progress_test_max]" class="form-control" placeholder="Progress test Max" value="'+ progressTestMax +'"></td>';
          row += '<td><input type="number" step="any" name="skill_grades['+ optionId +'][mid_max]" class="form-control" placeholder="Mid Max Grade" value="'+ midMax +'"></td>';
          row += '<td><input type="number" step="any" name="skill_grades['+ optionId +'][final_max]" class="form-control" placeholder="Final Max Grade" value="'+ finalMax +'"></td>';
          row += '</tr>';
          tbody.append(row);
        });
      }
      
      // Update the table on page load and when skill selections change.
      updateSkillsTable();
      $('#skills').on('change', function() {
        updateSkillsTable();
      });
    });
  </script>
@endpush
