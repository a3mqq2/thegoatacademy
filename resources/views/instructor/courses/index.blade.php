@extends('layouts.app')

@section('title', 'Courses')

@section('breadcrumb')
  <li class="breadcrumb-item">
    <a href="{{ route('admin.dashboard') }}">
      <i class="fa fa-tachometer-alt"></i> Dashboard
    </a>
  </li>
  <li class="breadcrumb-item active">
    <i class="fa fa-book"></i> Courses
  </li>
@endsection

@push('styles')
<style>
  :root {
    --primary-color:#6f42c1;
    --secondary-color:#007bff;
  }
  .status-badge{
    font-size:.75rem;
    padding:.35rem .55rem;
    border-radius:12px
  }
</style>
@endpush


@section('content')
<div class="container my-4">

  <div class="card shadow-sm">
    <div class="card-header bg-gradient" style="background:linear-gradient(135deg,var(--primary-color),var(--secondary-color));color:#fff">
      <h5 class="mb-0"><i class="fa fa-book-open"></i> Courses List</h5>
    </div>
    <div class="card-body p-0">

      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Course</th>
              <th>Instructor</th>
              <th>Start</th>
              <th>Mid&nbsp;Exam</th>
              <th>Final&nbsp;Exam</th>
              <th>Students</th>
              <th>Status</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($courses as $course)
            @php
              /* لون شارة الحالة */
              $stColor = [
                'upcoming' =>'warning',
                'ongoing'  =>'info',
                'completed'=>'success',
                'cancelled'=>'danger'
              ][$course->status] ?? 'secondary';
          
              /* محاضرة اليوم (إن وُجدت) */
              $todaySched = $course->schedules
                              ->where('date', now()->format('Y-m-d'))
                              ->first();
          
              /* هل بدأت المحاضرة؟ */
              $canTake = false;
              if($todaySched){
                  $startDT = \Carbon\Carbon::createFromFormat(
                              'Y-m-d H:i',
                              $todaySched->date.' '.$todaySched->from_time
                            );
                  $canTake = now()->greaterThanOrEqualTo($startDT);
              }
            @endphp
          
            <tr>
              <td>{{ $course->id }}</td>
              <td>{{ $course->courseType->name ?? 'N/A' }}</td>
              <td>{{ $course->instructor->name  ?? 'N/A' }}</td>
              <td>{{ $course->start_date }}</td>
              <td>{{ $course->mid_exam_date   ?: '-' }}</td>
              <td>{{ $course->final_exam_date ?: '-' }}</td>
              <td>{{ $course->student_count }}</td>
              <td>
                <span class="status-badge bg-{{ $stColor }}">{{ ucfirst($course->status) }}</span>
              </td>
          
              <td class="text-center">
                {{-- Show details --}}
                <a href="{{ route('instructor.courses.show', $course->id) }}"
                   class="btn btn-sm btn-outline-primary">
                  <i class="fa fa-eye"></i>
                </a>
          
                {{-- Take attendance (فقط إذا بدأت المحاضرة) --}}
                @if($todaySched && $canTake)
                  <a href="{{ route('instructor.courses.take_attendance', [
                          'course'         => $course->id,
                          'CourseSchedule' => $todaySched->id
                        ]) }}"
                     class="btn btn-sm btn-success">
                    <i class="fa fa-check"></i>
                  </a>
                @endif
          
                {{-- Delete --}}
                {{-- <button class="btn btn-sm btn-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteModal"
                        data-course-id="{{ $course->id }}">
                  <i class="fa fa-trash"></i>
                </button> --}}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center py-4">
                <i class="fa fa-exclamation-circle"></i> No courses found.
              </td>
            </tr>
          @endforelse
          
          </tbody>
        </table>
      </div>

    </div>
  </div>

  <div class="d-flex justify-content-center mt-3">
    {{ $courses->appends(request()->query())->links() }}
  </div>
</div>


{{-- ================= Cancel Modal ================= --}}
<div class="modal fade" id="cancelModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="cancelForm" method="POST">
      @csrf @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-ban"></i> Confirm Cancellation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <i class="fa fa-exclamation-triangle"></i> Are you sure you want to cancel this course?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fa fa-times"></i> No
          </button>
          <button type="submit" class="btn btn-danger">
            <i class="fa fa-check"></i> Yes, Cancel
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- ================= Delete Modal ================= --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="deleteForm" method="POST">
      @csrf @method('DELETE')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-trash-alt"></i> Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <i class="fa fa-exclamation-circle"></i> Are you sure you want to delete this course?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fa fa-times"></i> Cancel
          </button>
          <button type="submit" class="btn btn-danger">
            <i class="fa fa-check"></i> Yes, Delete
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection


@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded',()=>{
    /* ------- cancel ------- */
    document.querySelectorAll('[data-bs-target="#cancelModal"]').forEach(btn=>{
      btn.addEventListener('click',()=>{
        const id = btn.dataset.courseId;
        document.getElementById('cancelForm')
                .setAttribute('action',`/admin/courses/${id}/cancel`);
      });
    });
    /* ------- delete ------- */
    document.querySelectorAll('[data-bs-target="#deleteModal"]').forEach(btn=>{
      btn.addEventListener('click',()=>{
        const id = btn.dataset.courseId;
        document.getElementById('deleteForm')
                .setAttribute('action',`/admin/courses/${id}`);
      });
    });
  });
</script>
@endpush
