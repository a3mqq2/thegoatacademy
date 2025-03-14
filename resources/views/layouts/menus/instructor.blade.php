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
   <a href="{{route('instructor.courses', ['status' => 'upcoming'])}}" class="pc-link">
     <span class="pc-micon">
       <svg class="pc-icon">
         <use xlink:href="#custom-element-plus"></use>
       </svg>
     </span>
     <span class="pc-mtext">Upcoming Courses</span>
   </a>
</li>



