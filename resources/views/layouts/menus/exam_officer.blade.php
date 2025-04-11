<ul class="pc-navbar">
   <li class="pc-item">
       <a href="{{route('exam_officer.dashboard')}}" class="pc-link">
         <span class="pc-micon">
           <svg class="pc-icon">
             <use xlink:href="#custom-home"></use>
           </svg>
         </span>
         <span class="pc-mtext">Dashboard</span>
       </a>
   </li>


   @if (auth()->user()->permissions->contains('name','Exam Manager'))

   <li class="pc-item">
      <a href="{{route('exam_officer.courses.index')}}" class="pc-link">
        <span class="pc-micon">
          <svg class="pc-icon">
            <use xlink:href="#custom-mouse-circle"></use>
          </svg>
        </span>
        <span class="pc-mtext">Courses Table</span>
      </a>
  </li>

  @endif

  <li class="pc-item">
    <a href="{{route('exam_officer.exams.index')}}" class="pc-link">
      <span class="pc-micon">
        <svg class="pc-icon">
          <use xlink:href="#custom-element-plus"></use>
        </svg>
      </span>
      <span class="pc-mtext">Exams Table</span>
    </a>
</li>

@if (auth()->user()->permissions->contains('name','Exam Manager'))

<li class="pc-item">
  <a href="{{ route('exam_officer.logs') }}" class="pc-link">
    <span class="pc-micon">
      <i class="fa fa-book"></i>
    </span>
    <span class="pc-mtext">Exam Logs</span>
  </a>
</li>

@endif