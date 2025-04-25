@extends('layouts.app')
@section('title', "Course #{$course->id} Details")

@section('content')
<div class="container mt-4">
  <!-- Course Information Card -->
  <div class="card shadow-sm mb-4">
    <div class="card-header">
      <h4 class="mb-0">Course Details</h4>
    </div>
    <div class="card-body">
      <div class="table-responsive">
         <table class="table table-bordered">
            <tr>
              <th class="bg-primary text-light">ID</th>
              <th class="bg-primary text-light">Instructor</th>
              <th class="bg-primary text-light">Course Type</th>
              <th class="bg-primary text-light">Group Type</th>
            </tr>
            <tr>
             <td>{{ $course->id }}</td>
              <td>{{ optional($course->instructor)->name ?? 'N/A' }}</td>
              <td>{{ optional($course->courseType)->name ?? 'N/A' }}</td>
              <td>{{ optional($course->groupType)->name ?? 'N/A' }}</td>
            </tr>
            <tr>
             <th class="bg-primary text-light">Start Date</th>
             <th class="bg-primary text-light">Pre Test Date</th>
             <th class="bg-primary text-light">Mid Exam Date</th>
             <th class="bg-primary text-light">Final Exam Date</th>
            </tr>
            <tr>
              <td>{{ $course->start_date ?? '-' }}</td>
              <td>{{ $course->pre_test_date ?? '-' }}</td>
              <td>{{ $course->mid_exam_date ?? '-' }}</td>
              <td>{{ $course->final_exam_date ?? '-' }}</td>
            </tr>
           
            <tr>
              <th class="bg-primary text-light">End Date</th>
              <th class="bg-primary text-light">Student Capacity</th>
              <th class="bg-primary text-light">Status</th>
              <th class="bg-primary text-light">Days & Time</th>
    
            </tr>
            <tr>
             <td>{{ $course->end_date ?? '-' }}</td>
              <td>{{ $course->student_capacity ?? '-' }}</td>
              <td>{{ ucfirst($course->status) }}</td>
              @php
                [$start, $end] = explode(' - ', $course->time);
                $formattedStart = \Carbon\Carbon::createFromFormat('H:i', $start)->format('h:i A');
                $formattedEnd = \Carbon\Carbon::createFromFormat('H:i', $end)->format('h:i A');
                @endphp
                
              <td>{{ $course->days ?? '-' }}/ {{ $formattedStart }} - {{ $formattedEnd }} </td>
            </tr>
            <tr>
              <th class="bg-primary text-light" colspan="2">Meeting Platform</th>
              <th class="bg-primary text-light" colspan="2">WhatsApp Group Link</th>
            </tr>
            <tr>
             <td colspan="2">{{ optional($course->meetingPlatform)->name ?? '-' }}</td>
              <td colspan="2">{{ $course->whatsapp_group_link ?? '-' }}</td>
            </tr>
          </table>
      </div>
    </div>
  </div>

  <!-- Exams Table Card -->
  <div class="card shadow-sm">
    <div class="card-header">
      <h4 class="mb-0">Exams for this Course</h4>
    </div>
    <div class="card-body">
      @if($course->exams->count())
      <div class="table-responsive">
         <table class="table table-bordered align-middle">
             <thead class="table-light">
                 <tr>
                     <th>ID</th>
                     <th>Course / Type</th>
                     <th>Instructor</th>
                     <th>Exam Type</th>
                     <th>Status</th>
                     <th>Examiner</th>
                     <th>Exam Date</th>
                     <th>Time</th>
                     <th>Actions</th>
                 </tr>
             </thead>
             <tbody>


                

                 @foreach($course->exams as $exam)


                    @php
                        $isOverdue = $exam->status == "overdue";
                    @endphp

                     <tr  class="{{ $isOverdue ? 'table-danger' : '' }}" >
                         <td>{{ $exam->id }}</td>
                         @php 
                             $course = $exam->course;
                             $ctName = optional($course->courseType)->name;
                             $gtName = optional($course->groupType)->name;
                             $instructorName = optional($course->instructor)->name;

                         @endphp
                         <td  class="{{ $isOverdue ? 'table-danger' : '' }}">
                             <strong>(#{{ $course->id }})</strong>
                             @if($ctName) / {{ $ctName }} @endif
                             @if($gtName) / {{ $gtName }} @endif
                         </td>
                         <td>{{ $instructorName ?? '-' }}</td>
                         <td>{{ $exam->exam_type }}</td>
                         <td>
                             @if($exam->status == 'new')
                                 <span class="badge bg-info text-dark">New</span>
                             @elseif($exam->status == 'assigned')
                                 <span class="badge bg-warning text-dark">Assigned</span>
                             @elseif($exam->status == 'completed')
                                 <span class="badge bg-success">Completed</span>
                             @elseif($exam->status == "overdue")
                                 <span class="badge bg-danger">Overdue</span>
                             @endif
                         </td>
                         <td>{{ optional($exam->examiner)->name ?? '-' }}</td>
                         <td>{{ optional($exam->exam_date)->format('Y-m-d') }}</td>
                         <td>{{ $exam->time ?? '-' }}</td>
                         <td>
                            @php
                                // Convert exam_date to a Carbon instance if not null
                                $examDateObj = optional($exam->exam_date);
                                // Check if it's today
                                $isToday = $examDateObj->isToday() ?? false;
                    
                                // Check if the current user is the assigned examiner
                                $isExaminer = ($exam->examiner_id == Auth::id());
                            @endphp
                    
                            <!-- Logic:
                                 1) If exam is 'new'/'assigned' but exam_date is NOT today => show "Prepare / Edit" 
                                 2) If exam_date is today AND the user is the assigned examiner => show "رصد الدرجات" 
                                 3) If exam_date is today but user is not examiner => do nothing
                            -->


                            {{-- show route --}}
                            <a href="{{ route('exam_officer.exams.show', $exam->id) }}" class="btn btn-sm btn-info">
                                Show <i class="fa fa-eye"></i>
                            </a>


                                <!-- Show Prepare / Edit only if NOT today -->
                        
                                 @if ($exam->status == "new")
                                 <button 
                                 type="button" 
                                 class="btn btn-sm btn-primary prepExamBtn" 
                                 data-examid="{{ $exam->id }}"
                                 data-examtime="{{ $exam->time }}"
                                 data-examdate="{{ optional($exam->exam_date)->format('Y-m-d') }}"
                                 data-examinerid="{{ $exam->examiner_id ?? '' }}"
                                 data-status="{{ $exam->status }}"
                                 data-grammarmax="{{ $exam->grammar_max ?? 30 }}"
                                 data-vocabmax="{{ $exam->vocabulary_max ?? 40 }}"
                                 data-practicalmax="{{ $exam->practical_english_max ?? 10 }}"
                                 data-readingmax="{{ $exam->reading_max ?? 15 }}"
                                 data-writingmax="{{ $exam->writing_max ?? 15 }}"
                                 data-listeningmax="{{ $exam->listening_max ?? 10 }}"
                                 data-speakingmax="{{ $exam->speaking_max ?? 20 }}"
                             >
                                 @if($exam->status == 'new')
                                     Prepare <i class="fa fa-plus"></i>
                                 @else
                                     Edit Preparation <i class="fa fa-edit"></i>
                                 @endif
                             </button>
                                 @endif

                        </td>
                     </tr>
                 @endforeach
             </tbody>
         </table>
     </div>
      @else
      <p>No exams found for this course.</p>
      @endif
    </div>
  </div>
</div>
<div class="modal fade" id="prepExamModal" tabindex="-1" aria-labelledby="prepExamLabel" aria-hidden="true">
   <div class="modal-dialog">
       <form id="prepExamForm" method="POST" action="{{ route('exam_officer.exams.prepare') }}">
           @csrf
           <input type="hidden" name="exam_id" id="modal_exam_id">
           <input type="hidden" name="current_status" id="modal_current_status">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="prepExamLabel">Exam Preparation</h5>
                   <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
               </div>
               <div class="modal-body">
                   <!-- Examiner selection -->
                   <div class="mb-3">
                       <label for="modal_examiner_id" class="form-label">Examiner</label>
                       <select class="form-select" name="examiner_id" id="modal_examiner_id" required>
                           <option value="">Choose an Examiner</option>
                           @foreach($examiners as $ex)
                               <option value="{{ $ex->id }}">{{ $ex->name }}</option>
                           @endforeach
                       </select>
                   </div>
                   <!-- Time of exam -->
                   <div class="mb-3">
                       <label for="modal_exam_time" class="form-label">Exam Time</label>
                       <input type="time" class="form-control" name="time" id="modal_exam_time" required>
                   </div>

                   <!-- Date of exam -->
                   <div class="mb-3">
                       <label for="modal_exam_date" class="form-label">Exam Date</label>
                       <input type="date" class="form-control" name="exam_date" id="modal_exam_date" required>
                   </div>
                   <hr>

               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                   <button type="submit" class="btn btn-primary">Save Changes</button>
               </div>
           </div>
       </form>
   </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
   const prepButtons = document.querySelectorAll('.prepExamBtn');
   const examModal   = document.getElementById('prepExamModal');

   prepButtons.forEach(btn => {
       btn.addEventListener('click', () => {
           let examId     = btn.getAttribute('data-examid');
           let examTime   = btn.getAttribute('data-examtime') || '';
        //    let examDate   = btn.getAttribute('data-examdate') || '';
           let examinerId = btn.getAttribute('data-examinerid') || '';
           let currStatus = btn.getAttribute('data-status') || 'new';

           // Max grades
           let grammarMax     = btn.getAttribute('data-grammarmax') || '30';
           let vocabMax       = btn.getAttribute('data-vocabmax') || '40';
           let practicalMax   = btn.getAttribute('data-practicalmax') || '10';
           let readingMax     = btn.getAttribute('data-readingmax') || '15';
           let writingMax     = btn.getAttribute('data-writingmax') || '15';
           let listeningMax   = btn.getAttribute('data-listeningmax') || '10';
           let speakingMax    = btn.getAttribute('data-speakingmax') || '20';

           // Populate modal fields
           document.getElementById('modal_exam_id').value        = examId;
           document.getElementById('modal_exam_time').value      = examTime;
        //    document.getElementById('modal_exam_date').value      = examDate;
           document.getElementById('modal_examiner_id').value    = examinerId;
           document.getElementById('modal_current_status').value = currStatus;

           // Show the modal (Bootstrap 5)
           let modal = new bootstrap.Modal(examModal);
           modal.show();
       });
   });
});
</script>
@endpush
