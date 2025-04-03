@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css"/>
<style>
@media print {
  body { font-size: 12pt; color: #000; }
  table.dataTable, .table-bordered { border: 1px solid #000 !important; }
  table.dataTable th, table.dataTable td {
    border: 1px solid #000 !important;
    padding: 8px !important;
  }
  .dataTables_length, .dataTables_filter,
  .dataTables_info, .dataTables_paginate {
    display: none !important;
  }
}
</style>
@endpush

@section('content')
<div class="container">
  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Exam Officer Dashboard</h4>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="coursesTable" class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Instructor</th>
              <th>Group Type</th>
              <th>Course Type</th>
              <th>Days</th>
              <th>Time</th>
              <th>Mid Exam Date</th>
              <th>Final Exam Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($courses as $course)
            <tr>
              <td>{{ $course->id }}</td>
              <td>{{ optional($course->instructor)->name }}</td>
              <td>{{ optional($course->groupType)->name }}</td>
              <td>{{ optional($course->courseType)->name }}</td>
              <td>{{ $course->days }}</td>
              <td>{{ $course->time }}</td>
              <td>{{ $course->mid_exam_date }}</td>
              <td>{{ $course->final_exam_date }}</td>
              <td>
                <button
                  class="btn btn-sm btn-primary editExamDates"
                  data-id="{{ $course->id }}"
                  data-mid="{{ $course->mid_exam_date }}"
                  data-final="{{ $course->final_exam_date }}"
                >
                  Edit
                </button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="card shadow-sm mt-4">
        <div class="card-header">
          <h5 class="mb-0">Exam Dates Update Logs</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>User</th>
                  <th>Description</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                @forelse($logs as $log)
                <tr>
                  <td>{{ $log->id }}</td>
                  <td>{{ optional($log->user)->name }}</td>
                  <td>{{ $log->description }}</td>
                  <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="4" class="text-center">No logs found</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Modal for Editing Exam Dates -->
<div class="modal fade" id="examDateModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Exam Dates</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="examDateForm">
          <input type="hidden" name="course_id" id="course_id">
          <div class="mb-3">
            <label class="form-label">Mid Exam Date</label>
            <input type="date" class="form-control" name="mid_exam_date" id="mid_exam_date">
          </div>
          <div class="mb-3">
            <label class="form-label">Final Exam Date</label>
            <input type="date" class="form-control" name="final_exam_date" id="final_exam_date">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="saveExamDates">Save</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<script>
$(document).ready(function(){
  let table = $('#coursesTable').DataTable({
    dom: 'Bfrtip',
    buttons: [
      {
        extend: 'print',
        text: 'Print',
        title: 'Exam Officer Dashboard',
        customize: function (win) {
          $(win.document.body).css('font-size', '14px');
          $(win.document.body).find('table').addClass('compact').css('font-size', 'inherit');
        }
      },
      {
        extend: 'excelHtml5',
        text: 'Export to Excel',
        title: 'Exam_Officer_Dashboard'
      }
    ]
  });

  // Open modal with existing dates
  $(document).on('click', '.editExamDates', function(){
    $('#course_id').val($(this).data('id'));
    $('#mid_exam_date').val($(this).data('mid'));
    $('#final_exam_date').val($(this).data('final'));
    $('#examDateModal').modal('show');
  });

  // Save updated dates via Ajax
  $('#saveExamDates').on('click', function(){
    let courseId = $('#course_id').val();
    $.ajax({
      url: '/exam_officer/update-exam-dates/' + courseId,
      type: 'POST',
      data: {
        mid_exam_date: $('#mid_exam_date').val(),
        final_exam_date: $('#final_exam_date').val(),
        _token: '{{ csrf_token() }}'
      },
      success: function(resp) {
        if (resp.success) {
          let row = table.row($('button[data-id="'+courseId+'"]').closest('tr'));
          let data = row.data();
          data[6] = $('#mid_exam_date').val();
          data[7] = $('#final_exam_date').val();
          row.data(data).draw();
          $('#examDateModal').modal('hide');
        }
      }
    });
  });
});
</script>
@endpush
