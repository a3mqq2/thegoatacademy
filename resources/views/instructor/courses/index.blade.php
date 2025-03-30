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
    --primary-color: #6f42c1;
    --secondary-color: #007bff;
    --bg-color: #f0f2f5;
    --card-bg: #ffffff;
    --card-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    --transition-speed: 0.3s;
  }

  body {
    background: var(--bg-color);
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
  }

  /* Card container with relative positioning for shapes */
  .card {
    position: relative;
    border: none;
    border-radius: 15px;
    overflow: hidden;
    background-color: var(--card-bg);
    box-shadow: var(--card-shadow);
    transition: transform var(--transition-speed) ease, box-shadow var(--transition-speed) ease;
  }

  .card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
  }

  /* Infinite rotating shape in header */
  .card-header {
    position: relative;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    padding: 1rem;
    text-align: center;
    color: #fff;
    font-weight: 600;
    font-size: 1.1rem;
    overflow: hidden;
  }

  .card-header::before {
    content: "";
    position: absolute;
    top: -15px;
    left: -15px;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    animation: rotateInfinite 8s linear infinite;
  }

  @keyframes rotateInfinite {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }

  /* Floating shape in card body with infinite animation */
  .card-body {
    position: relative;
    padding: 1.5rem;
    font-size: 0.95rem;
    color: #333;
    background-color: #fafafa;
  }

  .card-body p {
    margin-bottom: 0.5rem;
  }

  .card-body .floating-shape {
    position: absolute;
    width: 80px;
    height: 80px;
    background: radial-gradient(circle, rgba(255,255,255,0.4), transparent);
    top: -20px;
    right: -20px;
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
    z-index: 0;
  }

  @keyframes float {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(-10px, 10px); }
  }

  /* Adding a pulsating shape to the card footer */
  .card-footer {
    position: relative;
    background: #fff;
    padding: 0.75rem;
    border-top: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-around;
  }

  .card-footer::after {
    content: "";
    position: absolute;
    bottom: -10px;
    right: -10px;
    width: 60px;
    height: 60px;
    background: rgba(0,0,0,0.05);
    border-radius: 50%;
    animation: pulse 2s infinite ease-in-out;
    z-index: -1;
  }

  @keyframes pulse {
    0% { transform: scale(1); opacity: 0.7; }
    50% { transform: scale(1.2); opacity: 1; }
    100% { transform: scale(1); opacity: 0.7; }
  }

  .card-footer a,
  .card-footer button {
    flex: 1;
    margin: 0 0.25rem;
    font-size: 0.85rem;
    padding: 0.5rem;
    border-radius: 8px;
    transition: background var(--transition-speed) ease, transform var(--transition-speed) ease;
  }

  .card-footer a:hover,
  .card-footer button:hover {
    transform: translateY(-3px);
  }

  .btn-primary {
    background: var(--primary-color);
    border: none;
  }

  .btn-warning {
    background: #ffc107;
    border: none;
  }

  .btn-danger {
    background: #dc3545;
    border: none;
  }

  .badge {
    font-size: 0.85rem;
    padding: 0.4em 0.6em;
    border-radius: 12px;
  }

  @media (max-width: 767.98px) {
    .card-body {
      padding: 1rem;
    }
    .card-footer a,
    .card-footer button {
      font-size: 0.8rem;
      padding: 0.4rem;
    }
  }
</style>
@endpush

@section('content')
<div class="container my-4">
  <div class="row">
    @forelse($courses as $course)
      <div class="col-md-4 mb-4">
        <div class="card animate__animated animate__fadeInUp">
          <div class="card-header">
            <i class="fa fa-book-open"></i> {{ $course->courseType->name ?? 'N/A' }}
          </div>
          <div class="card-body">
            <div class="floating-shape"></div>
            <p><strong><i class="fa fa-id-badge"></i> ID:</strong> {{ $course->id }}</p>
            <p><strong><i class="fa fa-user"></i> Instructor:</strong> {{ $course->instructor->name ?? 'N/A' }}</p>
            <p><strong><i class="fa fa-calendar-alt"></i> Start Date:</strong> {{ $course->start_date }}</p>
            <p><strong><i class="fa fa-pencil-alt"></i> Mid Exam:</strong> {{ $course->mid_exam_date }}</p>
            <p><strong><i class="fa fa-graduation-cap"></i> Final Exam:</strong> {{ $course->final_exam_date }}</p>
            <p><strong><i class="fa fa-users"></i> Students:</strong> {{ $course->student_count }}</p>
            <p>
              <strong><i class="fa fa-info-circle"></i> Status:</strong>
              <span class="badge bg-{{ 
                $course->status === 'upcoming'  ? 'warning' : (
                $course->status === 'ongoing'   ? 'info'    : (
                $course->status === 'completed' ? 'success' : (
                $course->status === 'cancelled' ? 'danger'  : 'secondary')))
              }}">
                {{ ucfirst($course->status) }}
              </span>
            </p>
          </div>
          <div class="card-footer">
            <a href="{{ route('instructor.courses.show', $course->id) }}" class="btn btn-primary btn-sm">
              <i class="fa fa-eye"></i> Show
            </a>
            {{-- Check if today's schedule exists for this course --}}
            @if($course->schedules->where('date', \Carbon\Carbon::now()->format('Y-m-d'))->isNotEmpty())
              <a href="{{ route('instructor.courses.take_attendance', ["course" => $course->id, "CourseSchedule" => $course->schedules->where('date', \Carbon\Carbon::now()->format('Y-m-d'))->first()]) }}" class="btn btn-success btn-sm">
                <i class="fa fa-check"></i> Take Attendance
              </a>
            @endif
          </div>
        </div>
      </div>
    @empty
      <div class="col-12">
        <div class="alert alert-info text-center">
          <i class="fa fa-exclamation-circle"></i> No courses found.
        </div>
      </div>
    @endforelse
  </div>
  <div class="d-flex justify-content-center">
    {{ $courses->appends(request()->query())->links() }}
  </div>
</div>

<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="cancelForm" method="POST">
      @csrf
      @method('PUT')
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="deleteForm" method="POST">
      @csrf
      @method('DELETE')
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
  document.addEventListener("DOMContentLoaded", function () {
    // Set action for Cancel Modal
    document.querySelectorAll('[data-bs-target="#cancelModal"]').forEach(btn => {
      btn.addEventListener("click", function () {
        let courseId = this.dataset.courseId;
        document.getElementById("cancelForm").setAttribute("action", "/admin/courses/" + courseId + "/cancel");
      });
    });
    
    // Set action for Delete Modal
    document.querySelectorAll('[data-bs-target="#deleteModal"]').forEach(btn => {
      btn.addEventListener("click", function () {
        let courseId = this.dataset.courseId;
        document.getElementById("deleteForm").setAttribute("action", "/admin/courses/" + courseId);
      });
    });
  });
</script>
@endpush
