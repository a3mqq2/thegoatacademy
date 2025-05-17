<li class="pc-item">
   <a href="{{route('instructor.dashboard')}}" class="pc-link">
     <span class="pc-micon">
       <svg class="pc-icon">
         <use xlink:href="#custom-home"></use>
       </svg>
     </span>
     <span class="pc-mtext">Dashboard</span>
   </a>
</li>


<li class="pc-item">
  <a href="{{route('instructor.courses.index', ['status' => 'ongoing'])}}" class="pc-link">
    <span class="pc-micon">
      <svg class="pc-icon">
        <use xlink:href="#custom-element-plus"></use>
      </svg>
    </span>
    <span class="pc-mtext">Ongoing Courses</span>
  </a>
</li>




<li class="pc-item">
  <a href="{{route('instructor.courses.index', ['status' => 'completed'])}}" class="pc-link">
    <span class="pc-micon">
      <svg class="pc-icon">
        <use xlink:href="#custom-element-plus"></use>
      </svg>
    </span>
    <span class="pc-mtext">Completed Courses</span>
  </a>
</li>





<li class="pc-item">
  <a href="{{route('instructor.profile')}}" class="pc-link">
    <span class="pc-micon">
      <svg class="pc-icon">
        <use xlink:href="#custom-user"></use>
      </svg>
    </span>
    <span class="pc-mtext"> My Profile  </span>
  </a>
</li>



