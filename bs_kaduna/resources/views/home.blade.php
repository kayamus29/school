@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-3">
                    <div class="col ps-4">
                        <!-- <h1 class="display-6 mb-3"><i class="ms-auto bi bi-grid"></i> {{ __('Dashboard') }}</h1> -->
                        <div class="row dashboard">
                            @if(Auth::user()->hasRole('Teacher'))
                                <div class="col-md-4">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <p class="text-muted text-uppercase small mb-1">My Students</p>
                                                    <h3 class="mb-0 fw-bold">{{$teacherAssignedStudentsCount}}</h3>
                                                </div>
                                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                                    <i class="bi bi-people-fill text-primary fs-4"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <p class="text-muted text-uppercase small mb-1">My Classes</p>
                                                    <h3 class="mb-0 fw-bold">{{$teacherAssignedClassesCount}}</h3>
                                                </div>
                                                <div class="bg-info bg-opacity-10 p-3 rounded">
                                                    <i class="bi bi-diagram-3 text-info fs-4"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <p class="text-muted text-uppercase small mb-1">Attendance</p>
                                                    <h3
                                                        class="mb-0 fw-bold @if($teacherTodayAttendanceDone) text-success @else text-warning @endif">
                                                        {{$teacherTodayAttendanceDone ? 'Done' : 'Pending'}}
                                                    </h3>
                                                </div>
                                                <div
                                                    class="@if($teacherTodayAttendanceDone) bg-success @else bg-warning @endif bg-opacity-10 p-3 rounded">
                                                    <i
                                                        class="bi bi-calendar-check @if($teacherTodayAttendanceDone) text-success @else text-warning @endif fs-4"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <p class="text-muted text-uppercase small mb-1">Total Students</p>
                                                    <h3 class="mb-0 fw-bold">{{$studentCount}}</h3>
                                                </div>
                                                <div class="bg-dark bg-opacity-10 p-3 rounded">
                                                    <i class="bi bi-person-lines-fill text-dark fs-4"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <p class="text-muted text-uppercase small mb-1">Total Teachers</p>
                                                    <h3 class="mb-0 fw-bold">{{$teacherCount}}</h3>
                                                </div>
                                                <div class="bg-dark bg-opacity-10 p-3 rounded">
                                                    <i class="bi bi-person-lines-fill text-dark fs-4"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if(Auth::user()->hasRole('Student'))
                                    <div class="col-md-3">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <p class="text-muted text-uppercase small mb-1">Balance</p>
                                                        <h3 class="mb-0 fw-bold text-danger">₦{{ number_format($outstandingBalance, 2) }}</h3>
                                                    </div>
                                                    <div class="bg-danger bg-opacity-10 p-3 rounded">
                                                        <i class="bi bi-wallet2 text-danger fs-4"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <p class="text-muted text-uppercase small mb-1">Total Classes</p>
                                                    <h3 class="mb-0 fw-bold">{{ $classCount }}</h3>
                                                </div>
                                                <div class="bg-dark bg-opacity-10 p-3 rounded">
                                                    <i class="bi bi-diagram-3 text-dark fs-4"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if($studentCount > 0)
                            <div class="mt-3 d-flex align-items-center">
                                <div class="col-3">
                                    <span class="ps-2 me-2">Students %</span>
                                    <span class="badge rounded-pill border" style="background-color: #0678c8;">Male</span>
                                    <span class="badge rounded-pill border" style="background-color: #49a4fe;">Female</span>
                                </div>
                                @php
                                    $maleStudentPercentage = round(($maleStudentsBySession / $studentCount), 2) * 100;
                                    $maleStudentPercentageStyle = "style='background-color: #0678c8; width: $maleStudentPercentage%'";

                                    $femaleStudentPercentage = round((($studentCount - $maleStudentsBySession) / $studentCount), 2) * 100;
                                    $femaleStudentPercentageStyle = "style='background-color: #49a4fe; width: $femaleStudentPercentage%'";
                                @endphp
                                <div class="col-9 progress">
                                    <div class="progress-bar progress-bar-striped" role="progressbar"
                                        {!!$maleStudentPercentageStyle!!} aria-valuenow="{{$maleStudentPercentage}}"
                                        aria-valuemin="0" aria-valuemax="100">{{$maleStudentPercentage}}%</div>
                                    <div class="progress-bar progress-bar-striped" role="progressbar"
                                        {!!$femaleStudentPercentageStyle!!} aria-valuenow="{{$femaleStudentPercentage}}"
                                        aria-valuemin="0" aria-valuemax="100">{{$femaleStudentPercentage}}%</div>
                                </div>
                            </div>
                        @endif
                        <div class="row align-items-md-stretch mt-4">
                            <div class="col">
                                <div class="p-3 text-white bg-dark rounded-3">
                                    <h3>Welcome to Unifiedtransform!</h3>
                                    <p><i class="bi bi-emoji-heart-eyes"></i> Thanks for your love and support.</p>
                                </div>
                            </div>
                            <div class="col">
                                <div class="p-3 bg-white border rounded-3" style="height: 100%;">
                                    <h3>Manage school better</h3>
                                    <p class="text-end">with <i class="bi bi-lightning"></i> <a
                                            href="https://github.com/changeweb/Unifiedtransform" target="_blank"
                                            style="text-decoration: none;">Unifiedtransform</a> <i
                                            class="bi bi-lightning"></i>.</p>
                                </div>
                            </div>
                        </div>

                        @if(Auth::user()->role == 'admin')
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card border-danger mb-3">
                                        <div
                                            class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                                            <span><i class="bi bi-person-x me-2"></i> Absent Staff Today</span>
                                            @if(!$isSchoolDay) <span class="badge bg-light text-danger">Weekend</span> @endif
                                        </div>
                                        <div class="card-body p-0">
                                            <ul class="list-group list-group-flush">
                                                @forelse($absentStaff as $staff)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        {{ $staff->first_name }} {{ $staff->last_name }}
                                                        <small class="text-muted">{{ $staff->phone }}</small>
                                                    </li>
                                                @empty
                                                    <li class="list-group-item text-center py-3 text-muted">
                                                        {{ $isSchoolDay ? 'All staff are checked in!' : 'No attendance required today.' }}
                                                    </li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-warning mb-3">
                                        <div
                                            class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                                            <span><i class="bi bi-person-dash me-2"></i> Absent Students Today</span>
                                            @if(!$isSchoolDay) <span class="badge bg-light text-dark">Weekend</span> @endif
                                        </div>
                                        <div class="card-body p-0">
                                            <ul class="list-group list-group-flush">
                                                @forelse($absentStudents as $attendance)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        {{ $attendance->student->first_name }} {{ $attendance->student->last_name }}
                                                        <span
                                                            class="badge bg-light text-dark">{{ $attendance->schoolClass->class_name }}</span>
                                                    </li>
                                                @empty
                                                    <li class="list-group-item text-center py-3 text-muted">
                                                        {{ $isSchoolDay ? 'No students marked absent yet.' : 'No school today.' }}
                                                    </li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="row mt-4">
                            <div class="col-lg-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-transparent"><i class="bi bi-calendar-event me-2"></i> Events
                                    </div>
                                    <div class="card-body text-dark">
                                        @include('components.events.event-calendar', ['editable' => 'false', 'selectable' => 'false'])
                                        {{-- <div class="overflow-auto" style="height: 250px;">
                                            <div class="list-group">
                                                <a href="#" class="list-group-item list-group-item-action">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <h5 class="mb-1">List group item heading</h5>
                                                        <small>3 days ago</small>
                                                    </div>
                                                    <p class="mb-1">Some placeholder content in a paragraph.</p>
                                                    <small>And some small print.</small>
                                                </a>
                                            </div>
                                        </div> --}}
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card mb-3">
                                    <div class="card-header bg-transparent d-flex justify-content-between"><span><i
                                                class="bi bi-megaphone me-2"></i> Notices</span> {{ $notices->links() }}
                                    </div>
                                    <div class="card-body p-0 text-dark">
                                        <div>
                                            @isset($notices)
                                                <div class="accordion accordion-flush" id="noticeAccordion">
                                                    @foreach ($notices as $notice)
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="flush-heading{{$notice->id}}">
                                                                <button class="accordion-button collapsed" type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#flush-collapse{{$notice->id}}"
                                                                    aria-expanded={{($loop->first) ? "true" : "false"}}
                                                                    aria-controls="flush-collapse{{$notice->id}}">
                                                                    Published at: {{$notice->created_at}}
                                                                </button>
                                                            </h2>
                                                            <div id="flush-collapse{{$notice->id}}"
                                                                class="accordion-collapse collapse {{($loop->first) ? "show" : "hide"}}"
                                                                aria-labelledby="flush-heading{{$notice->id}}"
                                                                data-bs-parent="#noticeAccordion">
                                                                <div class="accordion-body overflow-auto">
                                                                    {!!Purify::clean($notice->notice)!!}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                            @endisset
                                                @if(count($notices) < 1)
                                                    <div class="p-3">No notices</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection