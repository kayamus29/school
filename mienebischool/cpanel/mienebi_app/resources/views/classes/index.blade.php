@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-diagram-3"></i> Classes</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Classes</li>
                        </ol>
                    </nav>
                    <div class="row g-4">
                        @isset($school_classes)
                            @foreach ($school_classes as $school_class)
                            @php
                                $total_sections = 0;
                            @endphp
                                <div class="col-md-6 col-xxl-4 mb-4">
                                    <div class="card shadow-sm border-0 h-100">
                                        <div class="card-header bg-transparent border-bottom-0">
                                            <ul class="nav nav-tabs card-header-tabs">
                                                <li class="nav-item">
                                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#class{{$school_class->id}}" role="tab" aria-current="true"><i class="bi bi-diagram-3"></i> {{$school_class->class_name}}</button>
                                                </li>
                                                <li class="nav-item">
                                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#class{{$school_class->id}}-syllabus" role="tab" aria-current="false"><i class="bi bi-journal-text"></i> Syllabus</button>
                                                </li>
                                                <li class="nav-item">
                                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#class{{$school_class->id}}-courses" role="tab" aria-current="false"><i class="bi bi-journal-medical"></i> Courses</button>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="card-body text-dark">
                                            <div class="tab-content">
                                                <div class="tab-pane fade show active" id="class{{$school_class->id}}" role="tabpanel">
                                                    <div class="accordion" id="accordionClass{{$school_class->id}}">
                                                        @isset($school_sections)
                                                            @foreach ($school_sections as $school_section)
                                                                @if ($school_section->class_id == $school_class->id)
                                                                    @php
                                                                        $total_sections++;
                                                                    @endphp
                                                                    <div class="accordion-item">
                                                                        <h2 class="accordion-header" id="headingClass{{$school_class->id}}Section{{$school_section->id}}">
                                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionClass{{$school_class->id}}Section{{$school_section->id}}" aria-expanded="false" aria-controls="accordionClass{{$school_class->id}}Section{{$school_section->id}}">
                                                                            {{$school_section->section_name}}
                                                                        </button>
                                                                        </h2>
                                                                        <div id="accordionClass{{$school_class->id}}Section{{$school_section->id}}" class="accordion-collapse collapse" aria-labelledby="headingClass{{$school_class->id}}Section{{$school_section->id}}" data-bs-parent="#accordionClass{{$school_class->id}}">
                                                                            <div class="accordion-body">
                                                                                <p class="lead mb-2 d-flex justify-content-between">
                                                                                    <span>Room No: {{$school_section->room_no}}</span>
                                                                                    @can('edit sections')
                                                                                    <span><a href="{{route('section.edit', ['id' => $school_section->id])}}" role="button" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a></span>
                                                                                    @endcan
                                                                                </p>
                                                                                <div class="mb-3 px-3 py-2 bg-light rounded shadow-sm">
                                                                                    @php
                                                                                        $sectionTeacher = $school_class->assignedTeachers
                                                                                            ->where('section_id', $school_section->id)
                                                                                            ->whereNull('course_id')
                                                                                            ->first();
                                                                                    @endphp
                                                                                    <i class="bi bi-person-workspace text-primary"></i> <strong>Section Teacher:</strong>
                                                                                    <span class="ms-2 @if(!$sectionTeacher) text-muted @else text-dark fw-bold @endif">
                                                                                        {{ $sectionTeacher ? $sectionTeacher->teacher->first_name . ' ' . $sectionTeacher->teacher->last_name : 'Not Assigned' }}
                                                                                    </span>
                                                                                </div>
                                                                                <div class="list-group">
                                                                                    <a href="{{route('student.list.show', ['class_id' => $school_class->id, 'section_id' => $school_section->id, 'section_name' => $school_section->section_name])}}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                                                        View Students
                                                                                    </a>
                                                                                    <a href="{{route('section.routine.show', ['class_id' => $school_class->id, 'section_id' => $school_section->id])}}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                                                        View Routine
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        @endisset
                                                    </div>
                                                </div>
                                                <div class="tab-pane fade" id="class{{$school_class->id}}-syllabus" role="tabpanel">
                                                    @isset($school_class->syllabi)
                                                    <table class="table table-borderless">
                                                        <thead>
                                                        <tr>
                                                            <th scope="col">Syllabus Name</th>
                                                            <th scope="col">Actions</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach ($school_class->syllabi as $syllabus)
                                                            <tr>
                                                            <td>{{$syllabus->syllabus_name}}</td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    <a href="{{asset('storage/'.$syllabus->syllabus_file_path)}}" role="button" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i> Download</a>
                                                                </div>
                                                            </td>
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                    @endisset
                                                </div>
                                                <div class="tab-pane fade" id="class{{$school_class->id}}-courses" role="tabpanel">
                                                    @isset($school_class->courses)
                                                        <table class="table">
                                                            <thead>
                                                            <tr>
                                                                <th scope="col">Course Name</th>
                                                                <th scope="col">Type</th>
                                                                <th scope="col">Actions</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($school_class->courses as $course)
                                                            <tr>
                                                                <td>{{$course->course_name}}</td>
                                                                <td>{{$course->course_type}}</td>
                                                                <td>
                                                                    @can('edit courses')
                                                                    <a href="{{route('course.edit', ['id' => $course->id])}}" class="btn btn-sm btn-outline-primary" role="button"><i class="bi bi-pencil"></i> Edit</a>
                                                                    @endcan
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endisset
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-transparent d-flex justify-content-between border-top-0 mt-auto">
                                            @isset($total_sections)
                                                <span>Total Sections: {{$total_sections}}</span>
                                            @endisset
                                            <div>
                                                @can('assign teachers') 
                                                    {{-- Assuming a permission exists, or use 'edit classes' --}}
                                                    <button type="button" class="btn btn-sm btn-outline-dark me-2" data-bs-toggle="modal" data-bs-target="#assignTeacherModal{{$school_class->id}}">
                                                        <i class="bi bi-person-badge"></i> Assign Teachers
                                                    </button>
                                                @endcan
                                                @can('edit classes')
                                                <a href="{{route('class.edit', ['id' => $school_class->id])}}" class="btn btn-sm btn-outline-primary" role="button"><i class="bi bi-pencil"></i> Edit Class</a>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Assign Teacher Modal -->
                                <div class="modal fade" id="assignTeacherModal{{$school_class->id}}" tabindex="-1" aria-labelledby="assignTeacherModalLabel{{$school_class->id}}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="assignTeacherModalLabel{{$school_class->id}}">Assign Teachers - {{$school_class->class_name}}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{route('school.teacher.assign.bulk')}}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <input type="hidden" name="class_id" value="{{$school_class->id}}">
                                                    <input type="hidden" name="session_id" value="{{$school_class->session_id}}"> 
                                                    
                                                    <div class="alert alert-info py-2">
                                                        <i class="bi bi-info-circle me-2"></i> Assign teachers to each section and their respective courses.
                                                    </div>

                                                    <div class="accordion" id="modalAccordion{{$school_class->id}}">
                                                        @foreach ($school_sections as $school_section)
                                                            @if ($school_section->class_id == $school_class->id)
                                                                <div class="accordion-item mb-2 shadow-sm border">
                                                                    <h2 class="accordion-header" id="modalHeading{{$school_section->id}}">
                                                                        <button class="accordion-button @if(!$loop->first) collapsed @endif bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#modalCollapse{{$school_section->id}}">
                                                                            <i class="bi bi-layers me-2"></i> Section: <strong>{{$school_section->section_name}}</strong>
                                                                        </button>
                                                                    </h2>
                                                                    <div id="modalCollapse{{$school_section->id}}" class="accordion-collapse collapse @if($loop->first) show @endif" data-bs-parent="#modalAccordion{{$school_class->id}}">
                                                                        <div class="accordion-body">
                                                                            <!-- Section Teacher -->
                                                                            @php
                                                                                $currSectionTeacher = $school_class->assignedTeachers
                                                                                    ->where('section_id', $school_section->id)
                                                                                    ->whereNull('course_id')
                                                                                    ->first();
                                                                            @endphp
                                                                            <div class="row mb-4 align-items-center">
                                                                                <div class="col-md-4">
                                                                                    <label class="fw-bold"><i class="bi bi-person-workspace text-primary"></i> Section Teacher</label>
                                                                                    <p class="small text-muted mb-0">The primary overseer for this section.</p>
                                                                                </div>
                                                                                <div class="col-md-8">
                                                                                    <select class="form-select" name="section_teachers[{{$school_section->id}}]">
                                                                                        <option value="">-- Unassigned --</option>
                                                                                        @foreach($teachers as $teacher)
                                                                                            <option value="{{$teacher->id}}" 
                                                                                                @if($currSectionTeacher && $currSectionTeacher->teacher_id == $teacher->id) selected @endif>
                                                                                                {{$teacher->first_name}} {{$teacher->last_name}}
                                                                                            </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>

                                                                            <!-- Course Teachers for this section -->
                                                                            <div class="bg-white p-3 rounded border">
                                                                                <h6 class="border-bottom pb-2 mb-3"><i class="bi bi-journal-medical text-success"></i> Course Teachers for {{$school_section->section_name}}</h6>
                                                                                @if($school_class->courses->count() > 0)
                                                                                    <div class="table-responsive">
                                                                                        <table class="table table-sm table-hover align-middle">
                                                                                            <thead class="table-light small">
                                                                                                <tr>
                                                                                                    <th style="width: 40%">Course</th>
                                                                                                    <th>Assigned Teacher</th>
                                                                                                </tr>
                                                                                            </thead>
                                                                                            <tbody>
                                                                                                @foreach($school_class->courses as $course)
                                                                                                    @php
                                                                                                        $currCourseTeacher = $school_class->assignedTeachers
                                                                                                            ->where('section_id', $school_section->id)
                                                                                                            ->where('course_id', $course->id)
                                                                                                            ->first();
                                                                                                    @endphp
                                                                                                    <tr>
                                                                                                        <td>
                                                                                                            <span class="fw-bold">{{$course->course_name}}</span>
                                                                                                            <div class="text-muted" style="font-size: 0.75rem">{{$course->course_type}}</div>
                                                                                                        </td>
                                                                                                        <td>
                                                                                                            <select class="form-select form-select-sm" name="course_teachers[{{$school_section->id}}][{{$course->id}}]">
                                                                                                                <option value="">-- Select Teacher --</option>
                                                                                                                @foreach($teachers as $teacher)
                                                                                                                    <option value="{{$teacher->id}}"
                                                                                                                        @if($currCourseTeacher && $currCourseTeacher->teacher_id == $teacher->id) selected @endif>
                                                                                                                        {{$teacher->first_name}} {{$teacher->last_name}}
                                                                                                                    </option>
                                                                                                                @endforeach
                                                                                                            </select>
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                @endforeach
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </div>
                                                                                @else
                                                                                    <p class="text-center text-muted small my-3">No courses defined for this class.</p>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endisset
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection

