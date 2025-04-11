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
          
          <!-- Multiselect for Skills using Select2 -->
          <div class="col-md-6">
            <label for="skills" class="form-label"><i class="fa fa-code"></i> Skills to Develop</label>
            <select name="skills[]" id="skills" class="form-select @error('skills') is-invalid @enderror" multiple>
              @foreach($skills as $skill)
                <option value="{{ $skill->id }}" selected>
                  {{ $skill->name }}
                </option>
              @endforeach
            </select>
            @error('skills')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        
        <!-- جدول لإدخال درجات mid max و final max لكل مهارة -->
        <div class="row mt-4">
          <div class="col-md-12">
            <h5>Skill Grades Settings</h5>
            <table class="table table-bordered" id="skills-grades-table">
              <thead>
                <tr>
                  <th>Skill</th>
                  <th>Mid Max Grade</th>
                  <th>Final Max Grade</th>
                </tr>
              </thead>
              <tbody id="skills-grades-tbody">
                {{-- سيتم تعبئة الصفوف تلقائيًا باستخدام JavaScript --}}
              </tbody>
            </table>
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
@endpush

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#skills').select2({
        placeholder: 'Select skills',
        allowClear: true
      });

      // Function to update the skills grades table based on selected skills
      function updateSkillsTable() {
        var data = $('#skills').select2('data');
        var tbody = $('#skills-grades-tbody');
        tbody.empty();
        data.forEach(function(skill) {
          var optionId = skill.id;
          var skillName = skill.text;
          var row = '<tr>';
          // تضمين الـ skill_id عبر حقل مخفي داخل عمود اسم المهارة
          row += '<td>' + skillName + '<input type="hidden" name="skill_grades['+ optionId +'][skill_id]" value="'+ optionId +'"></td>';
          row += '<td><input type="number" step="any" name="skill_grades['+ optionId +'][mid_max]" class="form-control" placeholder="Mid Max Grade"></td>';
          row += '<td><input type="number" step="any" name="skill_grades['+ optionId +'][final_max]" class="form-control" placeholder="Final Max Grade"></td>';
          row += '</tr>';
          tbody.append(row);
        });
      }

      // تحديث الجدول عند تحميل الصفحة وعند تغيير الاختيارات
      updateSkillsTable();
      $('#skills').on('change', function() {
        updateSkillsTable();
      });
    });
  </script>
@endpush
