<ul class="pc-navbar">
  <li class="pc-item">
      <a href="{{route('admin.dashboard')}}" class="pc-link">
        <span class="pc-micon">
          <svg class="pc-icon">
            <use xlink:href="#custom-home"></use>
          </svg>
        </span>
        <span class="pc-mtext">Dashboard</span>
      </a>
  </li>
  
    @if (auth()->user()->permissions->where('name', 'Manage Users')->count())
      <li class="pc-item pc-hasmenu">
        <a href="#!" class="pc-link">
          <span class="pc-micon">
            <svg class="pc-icon">
              <use xlink:href="#custom-user"></use>
            </svg>
          </span>
          <span class="pc-mtext">Users</span><span class="pc-arrow"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg></span></a>
        <ul class="pc-submenu" style="display: none;">
          <li class="pc-item"><a class="pc-link" href="{{route('admin.users.create')}}">Create a new user</a></li>
          <li class="pc-item"><a class="pc-link" href="{{route('admin.users.index', ['status' => 'active'])}}">Show Active users</a></li>
          <li class="pc-item"><a class="pc-link" href="{{route('admin.users.index', ['status' => 'inactive'])}}">Show Inactive users</a></li>
          <li class="pc-item"><a class="pc-link" href="{{route('admin.users.index')}}">Show all users</a></li>
        </ul>
      </li>
    @endif

    
    @if (auth()->user()->permissions->where('name', 'Students List')->count())
    <li class="pc-item pc-hasmenu">
      <a href="#!" class="pc-link">
        <span class="pc-micon">
          <svg class="pc-icon">
            <use xlink:href="#custom-profile-2user-outline"></use>
          </svg>
        </span>
        <span class="pc-mtext">Students</span>
        <span class="pc-arrow">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </span>
      </a>
      <ul class="pc-submenu" style="display: none;">
        <li class="pc-item"><a class="pc-link" href="{{ route('admin.students.create') }}">Create a new Student</a></li>
        {{-- <li class="pc-item"><a class="pc-link" href="{{ route('admin.students.index', ['status' => 'ongoing']) }}">Show Ongoing Students</a></li> --}}
        {{-- <li class="pc-item"><a class="pc-link" href="{{ route('admin.students.index', ['status' => 'excluded']) }}">Show Excluded Students</a></li> --}}
        {{-- <li class="pc-item"><a class="pc-link" href="{{ route('admin.students.index', ['status' => 'withdrawn']) }}">Show Withdrawn Students</a></li> --}}
        <li class="pc-item"><a class="pc-link" href="{{ route('admin.students.index') }}">Show all Students</a></li>
      </ul>
    </li>
    @endif

    @if (auth()->user()->permissions->where('name', 'Courses List')->count())

    <li class="pc-item pc-hasmenu">
      <a href="#!" class="pc-link">
        <span class="pc-micon">
          <svg class="pc-icon">
            <use xlink:href="#custom-element-plus"></use>
          </svg>
        </span>
        <span class="pc-mtext">Courses</span>
        <span class="pc-arrow">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </span>
      </a>
      <ul class="pc-submenu" style="display: none;">
        <li class="pc-item"><a class="pc-link" href="{{ route('admin.courses.create') }}">Create New Course</a></li>
        <li class="pc-item"><a class="pc-link" href="{{ route('admin.courses.index', ['status' => 'ongoing']) }}">Ongoing Courses</a></li>
        <li class="pc-item"><a class="pc-link" href="{{ route('admin.courses.index', ['status' => 'completed']) }}">Completed Courses</a></li>
        <li class="pc-item"><a class="pc-link" href="{{ route('admin.courses.index', ['status' => 'cancelled']) }}">Cancelled Courses</a></li>
        <li class="pc-item"><a class="pc-link" href="{{ route('admin.courses.index') }}">All Courses</a></li>
      </ul>
    </li>
   
    @endif
    


    @if (auth()->user()->permissions->where('name', 'Reports')->count())
    
    <li class="pc-item">
      <a href="#" class="pc-link">
        <span class="pc-micon">
            <i class="ti ti-report-analytics"></i>
        </span>
        <span class="pc-mtext">Reports & Statictics </span>
      </a>
    </li>

    @endif



    @if (auth()->user()->permissions->where('name', 'Manage Settings')->count())
    <li class="pc-item pc-hasmenu">
      <a href="#!" class="pc-link">
        <span class="pc-micon">
          <i class="ti ti-manual-gearbox"></i>
        </span>
        <span class="pc-mtext">Settings</span><span class="pc-arrow"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg></span></a>
      <ul class="pc-submenu" style="display: none;">
        <li class="pc-item pc-hasmenu">
          <a href="#!" class="pc-link">
            <span class="pc-mtext">Withdrawn Reasons</span>
            <span class="pc-arrow">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                   viewBox="0 0 24 24" fill="none" stroke="currentColor"
                   stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                   class="feather feather-chevron-right">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </span>
          </a>
          <ul class="pc-submenu" style="display: none;">
            <li class="pc-item">
              <a class="pc-link" href="{{ route('admin.withdrawn_reasons.create') }}">Create New Reason</a>
            </li>
            <li class="pc-item">
              <a class="pc-link" href="{{ route('admin.withdrawn_reasons.index') }}">Show All Reasons</a>
            </li>
          </ul>
        </li>
        <li class="pc-item pc-hasmenu">
          <a href="#!" class="pc-link">
            <span class="pc-mtext">Exclude Reasons</span>
            <span class="pc-arrow">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" 
                   viewBox="0 0 24 24" fill="none" stroke="currentColor" 
                   stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                   class="feather feather-chevron-right">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </span>
          </a>
          <ul class="pc-submenu" style="display: none;">
            <li class="pc-item">
              <a class="pc-link" href="{{ route('admin.exclude_reasons.create') }}">Create New Reason</a>
            </li>
            <li class="pc-item">
              <a class="pc-link" href="{{ route('admin.exclude_reasons.index') }}">Show All Reasons</a>
            </li>
          </ul>
        </li>
        <li class="pc-item pc-hasmenu">
          <a href="#!" class="pc-link">
            <span class="pc-micon">
              <svg class="pc-icon">
                <use xlink:href="#custom-keyboard"></use>
              </svg>
            </span>
            <span class="pc-mtext">Course Types</span>
            <span class="pc-arrow">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </span>
          </a>
          <ul class="pc-submenu" style="display: none;">
            <li class="pc-item"><a class="pc-link" href="{{ route('admin.course-types.create') }}">Create a new Course Type</a></li>
            <li class="pc-item"><a class="pc-link" href="{{ route('admin.course-types.index') }}">Show all Course Types</a></li>
          </ul>
        </li>
        <li class="pc-item pc-hasmenu">
          <a href="#!" class="pc-link">
            <span class="pc-micon">
              <svg class="pc-icon">
                <use xlink:href="#custom-data"></use>
              </svg>
            </span>
            <span class="pc-mtext">Group Types</span>
            <span class="pc-arrow">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </span>
          </a>
          <ul class="pc-submenu" style="display: none;">
            <li class="pc-item"><a class="pc-link" href="{{ route('admin.group-types.create') }}">Create a new Group Type</a></li>
            <li class="pc-item"><a class="pc-link" href="{{ route('admin.group-types.index') }}">Show all Group Types</a></li>
          </ul>
        </li>
      </ul>
    </li>

    @endif
    
    @if (auth()->user()->permissions->where('name', 'Manage Quality Settings')->count())

    <li class="pc-item">
      <a href="{{ route('admin.quality-settings.index') }}" class="pc-link">
          <span class="pc-micon">
              <svg class="pc-icon">
                  <use xlink:href="#custom-setting-2"></use>
              </svg>
          </span>
          <span class="pc-mtext">Quality Settings</span>
      </a>
  </li>

  @endif

  
  @if (auth()->user()->permissions->where('name', 'Audit Logs')->count())

    <li class="pc-item">
      <a href="{{ route('admin.audit_logs.index') }}" class="pc-link">
        <span class="pc-micon">
            <i class="ti ti-history"></i>
        </span>
        <span class="pc-mtext">Audit Logs</span>
      </a>
    </li>

    @endif

    {{-- logout --}}
    <li class="pc-item">
      <a href="{{ route('logout') }}" class="pc-link">
        <span class="pc-micon">
            <i class="ti ti-power"></i>
        </span>
        <span class="pc-mtext">Logout</span>
      </a>
  
 </ul>