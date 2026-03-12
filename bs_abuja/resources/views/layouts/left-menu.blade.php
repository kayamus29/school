<div class="col-xs-12 col-sm-12 col-md-3 col-lg-2 border-rt-e6 px-0 no-print">
    <div class="d-flex flex-column align-items-center align-items-sm-start ">
        <ul class="nav flex-column pt-2 w-100">
            <li class="nav-item">
                @php
                    $dashboardUrl = url('home');
                    if (Auth::user()->hasRole('Accountant') || Auth::user()->role == 'accountant') {
                        $dashboardUrl = route('accounting.dashboard');
                    } elseif (Auth::user()->hasRole('Student')) {
                        $dashboardUrl = route('student.dashboard');
                    }
                @endphp
                <a class="nav-link {{ (request()->is('home') || request()->is('accounting/dashboard') || request()->is('portal/student')) ? 'active' : '' }}" href="{{ $dashboardUrl }}"><i
                        class="ms-auto bi bi-grid"></i> <span
                        class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">{{ __('Dashboard') }}</span></a>
            </li>
            @can('staff check-in')
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('staff.attendance.index') ? 'active' : '' }}" href="{{route('staff.attendance.index')}}">
                    <i class="bi bi-geo-alt"></i> <span
                        class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Check-In / Out</span></a>
            </li>
            @endcan
            {{-- @if (Auth::user()->role == "teacher")
            <li class="nav-item">
                <a type="button" href="{{url('attendances')}}"
                    class="d-flex nav-link {{ request()->is('attendances*')? 'active' : '' }}"><i
                        class="bi bi-calendar2-week"></i> <span
                        class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Attendance</span></a>
            </li>
            @endif --}}
            @can('view classes')
                <li class="nav-item">
                    @php
                        if (session()->has('browse_session_id')) {
                            $classCount = \App\Models\SchoolClass::where('session_id', session('browse_session_id'))->count();
                        } else {
                            $latest_session = \App\Models\SchoolSession::latest()->first();
                            if ($latest_session) {
                                $classCount = \App\Models\SchoolClass::where('session_id', $latest_session->id)->count();
                            } else {
                                $classCount = 0;
                            }
                        }
                    @endphp
                    <a class="nav-link d-flex {{ request()->is('classes') ? 'active' : '' }}" href="{{url('classes')}}"><i
                            class="bi bi-diagram-3"></i> <span
                            class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Classes</span> <span
                            class="ms-auto d-inline d-sm-none d-md-none d-xl-inline">{{ $classCount }}</span></a>
                </li>
            @endcan
            @if(Auth::user()->hasRole('Teacher'))
                <li class="nav-item">
                    <a type="button" href="#student-submenu" data-bs-toggle="collapse"
                        class="d-flex nav-link {{ request()->is('students*') ? 'active' : '' }}"><i
                            class="bi bi-people-fill"></i> <span
                            class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Students</span>
                        <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                    </a>
                    <ul class="nav collapse {{ request()->is('students*') ? 'show' : 'hide' }} bg-white"
                        id="student-submenu">
                        <li class="nav-item w-100" {{ request()->routeIs('student.list.show') ? 'style="font-weight:bold;"' : '' }}>
                            <a class="nav-link text-primary" href="{{route('student.list.show')}}">
                                <i class="bi bi-person-badge-fill me-2"></i> My Class Students
                            </a>
                        </li>
                    </ul>
                </li>
            @elseif(Auth::user()->hasRole('Admin'))
                <li class="nav-item">
                    <a type="button" href="#student-submenu" data-bs-toggle="collapse"
                        class="d-flex nav-link {{ request()->is('students*') ? 'active' : '' }}"><i
                            class="bi bi-person-lines-fill"></i> <span
                            class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Students</span>
                        <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                    </a>
                    <ul class="nav collapse {{ request()->is('students*') ? 'show' : 'hide' }} bg-white"
                        id="student-submenu">
                        <li class="nav-item w-100" {{ request()->routeIs('student.list.show') ? 'style="font-weight:bold;"' : '' }}><a class="nav-link" href="{{route('student.list.show')}}"><i
                                    class="bi bi-person-video2 me-2"></i> View Students</a></li>
                        @if (!session()->has('browse_session_id') && Auth::user()->hasRole('Admin'))
                            <li class="nav-item w-100" {{ request()->routeIs('student.create.show') ? 'style="font-weight:bold;"' : '' }}><a class="nav-link"
                                    href="{{route('student.create.show')}}"><i class="bi bi-person-plus me-2"></i> Add
                                    Student</a></li>
                        @endif
                    </ul>
                </li>
            @endif

            @if(Auth::user()->hasRole('Admin'))
                <li class="nav-item">
                    <a type="button" href="#teacher-submenu" data-bs-toggle="collapse"
                        class="d-flex nav-link {{ request()->is('teachers*') ? 'active' : '' }}"><i
                            class="bi bi-person-lines-fill"></i> <span
                            class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Teachers</span>
                        <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                    </a>
                    <ul class="nav collapse {{ request()->is('teachers*') ? 'show' : 'hide' }} bg-white"
                        id="teacher-submenu">
                        <li class="nav-item w-100" {{ request()->routeIs('teacher.list.show') ? 'style="font-weight:bold;"' : '' }}><a class="nav-link" href="{{route('teacher.list.show')}}"><i
                                    class="bi bi-person-video2 me-2"></i> View Teachers</a></li>
                        @if (!session()->has('browse_session_id') && Auth::user()->hasRole('Admin'))
                            <li class="nav-item w-100" {{ request()->routeIs('teacher.create.show') ? 'style="font-weight:bold;"' : '' }}><a class="nav-link"
                                    href="{{route('teacher.create.show')}}"><i class="bi bi-person-plus me-2"></i> Add
                                    Teacher</a></li>
                        @endif
                    </ul>
                </li>
            @endif
            @if(Auth::user()->hasRole('Teacher'))
                <li class="nav-item">
                    <a class="nav-link {{ (request()->is('courses/teacher*') || request()->is('courses/assignments*')) ? 'active' : '' }}"
                        href="{{route('course.teacher.list.show', ['teacher_id' => Auth::user()->id])}}"><i
                            class="bi bi-journal-check"></i> <span
                            class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">My Courses</span></a>
                </li>
            @endif
            @if(Auth::user()->hasRole('Student'))
                        <li class="nav-item">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('student.attendance') ? 'active' : '' }}"
                                href="{{route('student.attendance')}}"><i
                                    class="bi bi-calendar2-week"></i> <span
                                    class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Attendance</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('results.student') ? 'active' : '' }}"
                                href="{{route('results.student')}}"><i
                                    class="bi bi-graph-up-arrow"></i> <span
                                    class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Results Dashboard</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('student.timetable') ? 'active' : '' }}"
                                href="{{route('student.timetable')}}"><i
                                    class="bi bi-calendar4-range"></i> <span
                                    class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Timetable</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('course.student.list.show') ? 'active' : '' }}"
                                href="{{route('course.student.list.show', ['student_id' => Auth::user()->id])}}"><i
                                    class="bi bi-journal-medical"></i> <span
                                    class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Courses</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('student.fees') ? 'active' : '' }}"
                                href="{{route('student.fees')}}"><i
                                    class="bi bi-wallet2"></i> <span
                                    class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">My Fees</span></a>
                        </li>

            @endif
            @if(!Auth::user()->hasRole('Student') && !Auth::user()->hasRole('Accountant') && Auth::user()->role != 'accountant' && !Auth::user()->hasRole('Staff') && Auth::user()->role != 'staff')
                <li class="nav-item border-bottom">
                    <a type="button" href="#exam-grade-submenu" data-bs-toggle="collapse"
                        class="d-flex nav-link {{ request()->is('exams*') ? 'active' : '' }}"><i
                            class="bi bi-file-text"></i>
                        <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Exams / Grades</span>
                        <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                    </a>
                    <ul class="nav collapse {{ request()->is('exams*') ? 'show' : 'hide' }} bg-white"
                        id="exam-grade-submenu">
                        <li class="nav-item w-100" {{ request()->routeIs('exam.list.show') ? 'style="font-weight:bold;"' : '' }}><a class="nav-link" href="{{route('exam.list.show')}}"><i
                                    class="bi bi-file-text me-2"></i>
                                View Exams</a></li>
                        {{-- @if (Auth::user()->hasAnyRole(['Admin', 'Teacher']))
                            <li class="nav-item w-100" {{ request()->routeIs('exam.create.show') ? 'style="font-weight:bold;"' : '' }}><a class="nav-link" href="{{route('exam.create.show')}}"><i
                                        class="bi bi-file-plus me-2"></i> Create Exams</a></li>
                        @endif --}}
                        @if (Auth::user()->hasRole('Admin'))
                            <li class="nav-item w-100" {{ request()->routeIs('exam.grade.system.create') ? 'style="font-weight:bold;"' : '' }}><a class="nav-link"
                                    href="{{route('exam.grade.system.create')}}"><i class="bi bi-file-plus me-2"></i> Add Grade
                                    Systems</a></li>
                        @endif
                        <li class="nav-item w-100" {{ request()->routeIs('exam.grade.system.index') ? 'style="font-weight:bold;"' : '' }}><a class="nav-link"
                                href="{{route('exam.grade.system.index')}}"><i class="bi bi-file-ruled me-2"></i> View Grade
                                Systems</a></li>
                    </ul>
                </li>
                <li class="nav-item border-bottom">
                    <a type="button" href="#results-dashboard-submenu" data-bs-toggle="collapse"
                        class="d-flex nav-link {{ request()->is('results*') ? 'active' : '' }}"><i
                            class="bi bi-graph-up-arrow"></i>
                        <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Results Analysis</span>
                        <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                    </a>
                    <ul class="nav collapse {{ request()->is('results*') ? 'show' : 'hide' }} bg-white"
                        id="results-dashboard-submenu">
                        @if(Auth::user()->hasRole('Teacher'))
                            <li class="nav-item w-100" {{ request()->routeIs('results.teacher') ? 'style="font-weight:bold;"' : '' }}>
                                <a class="nav-link" href="{{route('results.teacher')}}">
                                    <i class="bi bi-layout-text-window-reverse me-2"></i> Subject Course Results
                                </a>
                            </li>
                            <li class="nav-item w-100" {{ request()->routeIs('results.section') ? 'style="font-weight:bold;"' : '' }}>
                                <a class="nav-link" href="{{route('results.section')}}">
                                    <i class="bi bi-person-badge-fill me-2"></i> Section Performance
                                </a>
                            </li>
                        @endif
                        @if(Auth::user()->hasRole('Admin'))
                            <li class="nav-item w-100" {{ request()->routeIs('results.admin') ? 'style="font-weight:bold;"' : '' }}>
                                <a class="nav-link" href="{{route('results.admin')}}">
                                    <i class="bi bi-search me-2"></i> Admin Search Audit
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            @if (Auth::user()->hasRole('Admin'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('notice*') ? 'active' : '' }}" href="{{route('notice.create')}}"><i
                            class="bi bi-megaphone"></i> <span
                            class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Notice</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('calendar-event*') ? 'active' : '' }}"
                        href="{{route('events.show')}}"><i class="bi bi-calendar-event"></i> <span
                            class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Event</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('syllabus*') ? 'active' : '' }}"
                        href="{{route('class.syllabus.create')}}"><i class="bi bi-journal-text"></i> <span
                            class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Syllabus</span></a>
                </li>
                <li class="nav-item border-bottom">
                    <a class="nav-link {{ request()->is('routine*') ? 'active' : '' }}"
                        href="{{route('section.routine.create')}}"><i class="bi bi-calendar4-range"></i> <span
                            class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Routine</span></a>
                </li>
            @endif
            @if (Auth::user()->hasRole('Admin'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('academics*') ? 'active' : '' }}"
                        href="{{url('academics/settings')}}"><i class="bi bi-tools"></i> <span
                            class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Academic</span></a>
                </li>
            @endif
                {{-- Accountants do not need Promotion/Graduation access --}}
                @if(!Auth::user()->hasRole('Accountant') && Auth::user()->role != 'accountant')
                <li class="nav-item border-bottom">
                    <a type="button" href="#promotions-submenu" data-bs-toggle="collapse"
                        class="d-flex nav-link {{ request()->is('promotions*') ? 'active' : '' }}"><i
                            class="bi bi-sort-numeric-up-alt"></i>
                        <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Promotions</span>
                        <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                    </a>
                    <ul class="nav collapse {{ request()->is('promotions*') ? 'show' : 'hide' }} bg-white"
                        id="promotions-submenu">
                        @if(Auth::user()->hasRole('Admin'))
                            <li class="nav-item w-100" {{ request()->routeIs('promotions.policy') ? 'style="font-weight:bold;"' : '' }}>
                                <a class="nav-link" href="{{route('promotions.policy')}}">
                                    <i class="bi bi-gear-fill me-2"></i> Policies
                                </a>
                            </li>
                            <li class="nav-item w-100" {{ request()->routeIs('promotions.index') ? 'style="font-weight:bold;"' : '' }}>
                                <a class="nav-link" href="{{route('promotions.index')}}">
                                    <i class="bi bi-arrow-repeat me-2"></i> Manual Legacy
                                </a>
                            </li>
                        @endif
                        @if(!Auth::user()->hasRole('Student'))
                        <li class="nav-item w-100" {{ request()->routeIs('promotions.review') ? 'style="font-weight:bold;"' : '' }}>
                            <a class="nav-link" href="{{route('promotions.review')}}">
                                <i class="bi bi-shield-check me-2"></i> Review Board
                            </a>
                        </li>
                        @endif
                        @if(Auth::user()->hasRole('Admin'))
                            <li class="nav-item w-100" {{ request()->routeIs('academics.graduation.index') ? 'style="font-weight:bold;"' : '' }}>
                                <a class="nav-link" href="{{route('academics.graduation.index')}}">
                                    <i class="bi bi-mortarboard-fill me-2"></i> Graduation Dash
                                </a>
                            </li>
                        @endif
                        @if(Auth::user()->hasRole('Student'))
                            <li class="nav-item w-100" {{ request()->routeIs('promotions.student.projection') ? 'style="font-weight:bold;"' : '' }}>
                                <a class="nav-link" href="{{route('promotions.student.projection')}}">
                                    <i class="bi bi-graph-up me-2"></i> My Projection
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
                @endif
            @if (Auth::user()->hasAnyRole(['Admin', 'Accountant']))
                <li class="nav-item">
                    <a type="button" href="#accounting-submenu" data-bs-toggle="collapse"
                        class="d-flex nav-link {{ request()->is('accounting*') ? 'active' : '' }}"><i
                            class="bi bi-wallet2"></i> <span
                            class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Accounting</span>
                        <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                    </a>
                    <ul class="nav collapse {{ request()->is('accounting*') ? 'show' : 'hide' }} bg-white"
                        id="accounting-submenu">
                        <li class="nav-item w-100" {{ request()->routeIs('accounting.dashboard') ? 'style="font-weight:bold;"' : '' }}><a class="nav-link"
                                href="{{route('accounting.dashboard')}}"><i class="bi bi-speedometer2 me-2"></i>
                                Summary</a></li>
                        <li class="nav-item w-100" {{ request()->routeIs('accounting.analytics.index') ? 'style="font-weight:bold;"' : '' }}><a class="nav-link text-success"
                                href="{{route('accounting.analytics.index')}}"><i class="bi bi-graph-up-arrow me-2"></i>
                                Analytics</a></li>
                        <li class="nav-item w-100" {{ request()->routeIs('accounting.fees.heads.index') ? 'style="font-weight:bold;"' : '' }}><a class="nav-link"
                                href="{{route('accounting.fees.heads.index')}}"><i class="bi bi-list-check me-2"></i> Fee
                                Heads</a></li>
                        <li class="nav-item w-100" {{ request()->routeIs('accounting.fees.class.index') ? 'style="font-weight:bold;"' : '' }}><a class="nav-link"
                                href="{{route('accounting.fees.class.index')}}"><i class="bi bi-diagram-3 me-2"></i> Assign
                                Fees</a></li>
                        <li class="nav-item w-100" {{ request()->routeIs('accounting.debtors.index') ? 'style="font-weight:bold;"' : '' }}><a class="nav-link text-danger"
                                href="{{route('accounting.debtors.index')}}"><i class="bi bi-exclamation-circle me-2"></i> Debtors List</a></li>
                            <li class="nav-item w-100" {{ request()->is('accounting/fees/student*') ? 'style="font-weight:bold;"' : '' }}><a class="nav-link"
                                    href="{{route('accounting.fees.student.index')}}"><i class="bi bi-plus-square me-2"></i> Addons</a></li>
                        <li class="nav-item w-100" {{ request()->routeIs('accounting.payments.index') ? 'style="font-weight:bold;"' : '' }}><a class="nav-link"
                                href="{{route('accounting.payments.index')}}"><i class="bi bi-currency-dollar me-2"></i>
                                Collect Fees</a></li>
                        <li class="nav-item w-100" {{ request()->routeIs('accounting.expenses.index') ? 'style="font-weight:bold;"' : '' }}><a class="nav-link"
                                href="{{route('accounting.expenses.index')}}"><i class="bi bi-cart-x me-2"></i> Expenses</a>
                        </li>
                    </ul>
                </li>
            @endif
            @if (Auth::user()->hasRole('Admin'))
                <li class="nav-item">
                    <a type="button" href="#staff-submenu" data-bs-toggle="collapse"
                        class="d-flex nav-link {{ request()->is('staff*') ? 'active' : '' }}"><i
                            class="bi bi-person-badge"></i> <span
                            class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Staff</span>
                        <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                    </a>
                    <ul class="nav collapse {{ request()->is('staff*') ? 'show' : 'hide' }} bg-white"
                        id="staff-submenu">
                        <li class="nav-item w-100" {{ request()->routeIs('staff.index') ? 'style="font-weight:bold;"' : '' }}><a class="nav-link" href="{{route('staff.index')}}"><i
                                    class="bi bi-person-video2 me-2"></i> View Staff</a></li>
                        <li class="nav-item w-100" {{ request()->routeIs('staff.create') ? 'style="font-weight:bold;"' : '' }}><a class="nav-link"
                                href="{{route('staff.create')}}"><i class="bi bi-person-plus me-2"></i> Add
                                Staff</a></li>
                    </ul>
                </li>
                @can('view audit logs')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('audit.index') ? 'active' : '' }}" href="{{route('audit.index')}}"><i
                            class="bi bi-clock-history"></i> <span
                            class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Audit Logs</span></a>
                </li>
                @endcan
            @endif

            @if(Auth::user()->hasRole('Staff') || Auth::user()->role == 'staff')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('accounting.expenses.my') ? 'active' : '' }}" href="{{route('accounting.expenses.my')}}">
                        <i class="bi bi-receipt"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">My Expenses</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
