@extends('layouts.app')

@section('content')
    <script src="{{ asset('js/masonry.pkgd.min.js') }}"></script>
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-2">
                    <div class="col ps-4">
                        <h1 class="display-6 mb-3">
                            <i class="bi bi-tools"></i> Academic Settings
                        </h1>

                        @include('session-messages')

                        <div class="mb-4">
                            <div class="row" data-masonry='{"percentPosition": true }'>
                                @if ($latest_school_session_id == $current_school_session_id)
                                    <div class="col-md-4 mb-4">
                                        <div class="p-3 border bg-light shadow-sm">
                                            <h6>Create Session</h6>
                                            <p class="text-danger">
                                                <small><i class="bi bi-exclamation-diamond-fill me-2"></i> Create one Session
                                                    per academic year. Last created session will be considered as the latest
                                                    academic session.</small>
                                            </p>
                                            <form action="{{route('school.session.store')}}" method="POST">
                                                @csrf
                                                <div class="mb-3">
                                                    <input type="text" class="form-control form-control-sm"
                                                        placeholder="2021 - 2022" aria-label="Current Session"
                                                        name="session_name" required>
                                                </div>
                                                <button class="btn btn-sm btn-outline-primary" type="submit"><i
                                                        class="bi bi-check2"></i> Create</button>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-4 mb-4">
                                    <div class="p-3 border bg-light shadow-sm">
                                        <h6>Browse by Session</h6>
                                        <p class="text-danger">
                                            <small><i class="bi bi-exclamation-diamond-fill me-2"></i> Only use this when
                                                you want to browse data from previous Sessions.</small>
                                        </p>
                                        <form action="{{route('school.session.browse')}}" method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <p class="mt-2">Select "Session" to browse by:</p>
                                                <select class="form-select form-select-sm" aria-label=".form-select-sm"
                                                    name="session_id" required>
                                                    @isset($school_sessions)
                                                        @foreach ($school_sessions as $school_session)
                                                            <option value="{{$school_session->id}}">
                                                                {{$school_session->session_name}}</option>
                                                        @endforeach
                                                    @endisset
                                                </select>
                                            </div>
                                            <button class="btn btn-sm btn-outline-primary" type="submit"><i
                                                    class="bi bi-check2"></i> Set</button>
                                        </form>
                                    </div>
                                </div>
                                @if ($latest_school_session_id == $current_school_session_id)
                                    <div class="col-md-4 mb-4">
                                        <div class="p-3 border bg-light shadow-sm">
                                            <h6>Create Semester for Current Session</h6>
                                            <form action="{{route('school.semester.create')}}" method="POST">
                                                @csrf
                                                <input type="hidden" name="session_id" value="{{$current_school_session_id}}">
                                                <div class="mt-2">
                                                    <p>Semester name<sup><i class="bi bi-asterisk text-primary"></i></sup></p>
                                                    <input type="text" class="form-control form-control-sm"
                                                        placeholder="First Semester" aria-label="Semester name"
                                                        name="semester_name" required>
                                                </div>
                                                <div class="mt-2">
                                                    <label for="inputStarts" class="form-label">Starts<sup><i
                                                                class="bi bi-asterisk text-primary"></i></sup></label>
                                                    <input type="date" class="form-control form-control-sm" id="inputStarts"
                                                        placeholder="Starts" name="start_date" required>
                                                </div>
                                                <div class="mt-2">
                                                    <label for="inputEnds" class="form-label">Ends<sup><i
                                                                class="bi bi-asterisk text-primary"></i></sup></label>
                                                    <input type="date" class="form-control form-control-sm" id="inputEnds"
                                                        placeholder="Ends" name="end_date" required>
                                                </div>
                                                <button type="submit" class="mt-3 btn btn-sm btn-outline-primary"><i
                                                        class="bi bi-check2"></i> Create</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <div class="p-3 border bg-light shadow-sm">
                                            <h6>Attendance Type</h6>
                                            <p class="text-danger">
                                                <small><i class="bi bi-exclamation-diamond-fill me-2"></i> Do not change the
                                                    type in the middle of a Semester.</small>
                                            </p>
                                            <form action="{{route('school.attendance.type.update')}}" method="POST">
                                                @csrf
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="attendance_type"
                                                        id="attendance_type_section"
                                                        {{(optional($academic_setting)->attendance_type == 'section') ? 'checked="checked"' : null}}
                                                        value="section">
                                                    <label class="form-check-label" for="attendance_type_section">
                                                        Attendance by Section
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="attendance_type"
                                                        id="attendance_type_course"
                                                        {{(optional($academic_setting)->attendance_type == 'course') ? 'checked="checked"' : null}}
                                                        value="course">
                                                    <label class="form-check-label" for="attendance_type_course">
                                                        Attendance by Course
                                                    </label>
                                                </div>

                                                <button type="submit" class="mt-3 btn btn-sm btn-outline-primary"><i
                                                        class="bi bi-check2"></i> Save</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <div class="p-3 border bg-light shadow-sm">
                                            <h6>Create Class</h6>
                                            <form action="{{route('school.class.create')}}" method="POST">
                                                @csrf
                                                <input type="hidden" name="session_id" value="{{$current_school_session_id}}">
                                                <div class="mb-3">
                                                    <input type="text" class="form-control form-control-sm" name="class_name"
                                                        placeholder="Class name" aria-label="Class name" required>
                                                </div>
                                                <button class="btn btn-sm btn-outline-primary" type="submit"><i
                                                        class="bi bi-check2"></i> Create</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <div class="p-3 border bg-light shadow-sm">
                                            <h6>Create Section</h6>
                                            <form action="{{route('school.section.create')}}" method="POST">
                                                @csrf
                                                <input type="hidden" name="session_id" value="{{$current_school_session_id}}">
                                                <div class="mb-3">
                                                    <input class="form-control form-control-sm" name="section_name" type="text"
                                                        placeholder="Section name" required>
                                                </div>
                                                <div class="mb-3">
                                                    <input class="form-control form-control-sm" name="room_no" type="text"
                                                        placeholder="Room No." required>
                                                </div>
                                                <div>
                                                    <p>Assign section to class:</p>
                                                    <select class="form-select form-select-sm" aria-label=".form-select-sm"
                                                        name="class_id" required>
                                                        @isset($school_classes)
                                                            @foreach ($school_classes as $school_class)
                                                                <option value="{{$school_class->id}}">{{$school_class->class_name}}
                                                                </option>
                                                            @endforeach
                                                        @endisset
                                                    </select>
                                                </div>
                                                <button type="submit" class="mt-3 btn btn-sm btn-outline-primary"><i
                                                        class="bi bi-check2"></i> Save</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <div class="p-3 border bg-light shadow-sm">
                                            <h6>Create Course</h6>
                                            <form action="{{route('school.course.create')}}" method="POST">
                                                @csrf
                                                <input type="hidden" name="session_id" value="{{$current_school_session_id}}">
                                                <div class="mb-1">
                                                    <input type="text" class="form-control form-control-sm" name="course_name"
                                                        placeholder="Course name" aria-label="Course name" required>
                                                </div>
                                                <div class="mb-3">
                                                    <p class="mt-2">Course Type:<sup><i
                                                                class="bi bi-asterisk text-primary"></i></sup></p>
                                                    <select class="form-select form-select-sm" name="course_type"
                                                        aria-label=".form-select-sm" required>
                                                        <option value="Core">Core</option>
                                                        <option value="General">General</option>
                                                        <option value="Elective">Elective</option>
                                                        <option value="Optional">Optional</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <p>Assign to semester:<sup><i class="bi bi-asterisk text-primary"></i></sup>
                                                    </p>
                                                    <select class="form-select form-select-sm" aria-label=".form-select-sm"
                                                        name="semester_id" required>
                                                        @isset($semesters)
                                                            @foreach ($semesters as $semester)
                                                                <option value="{{$semester->id}}">{{$semester->semester_name}}</option>
                                                            @endforeach
                                                        @endisset
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <p>Assign to class:<sup><i class="bi bi-asterisk text-primary"></i></sup>
                                                    </p>
                                                    <select class="form-select form-select-sm" aria-label=".form-select-sm"
                                                        name="class_id" required>
                                                        @isset($school_classes)
                                                            @foreach ($school_classes as $school_class)
                                                                <option value="{{$school_class->id}}">{{$school_class->class_name}}
                                                                </option>
                                                            @endforeach
                                                        @endisset
                                                    </select>
                                                </div>
                                                <button class="btn btn-sm btn-outline-primary" type="submit"><i
                                                        class="bi bi-check2"></i> Create</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <div class="p-3 border bg-light shadow-sm">
                                            <h6>Assign Teacher</h6>
                                            <form action="{{route('school.teacher.assign')}}" method="POST">
                                                @csrf
                                                <input type="hidden" name="session_id" value="{{$current_school_session_id}}">
                                                <div class="mb-3">
                                                    <p class="mt-2">Select Teacher:<sup><i
                                                                class="bi bi-asterisk text-primary"></i></sup></p>
                                                    <select class="form-select form-select-sm" aria-label=".form-select-sm"
                                                        name="teacher_id" required>
                                                        @isset($teachers)
                                                            @foreach ($teachers as $teacher)
                                                                <option value="{{$teacher->id}}">{{$teacher->first_name}}
                                                                    {{$teacher->last_name}}</option>
                                                            @endforeach
                                                        @endisset
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <p>Assign to semester:<sup><i class="bi bi-asterisk text-primary"></i></sup>
                                                    </p>
                                                    <select class="form-select form-select-sm" aria-label=".form-select-sm"
                                                        name="semester_id" required>
                                                        @isset($semesters)
                                                            @foreach ($semesters as $semester)
                                                                <option value="{{$semester->id}}">{{$semester->semester_name}}</option>
                                                            @endforeach
                                                        @endisset
                                                    </select>
                                                </div>
                                                <div>
                                                    <p>Assign to class:<sup><i class="bi bi-asterisk text-primary"></i></sup>
                                                    </p>
                                                    <select onchange="getSectionsAndCourses(this);"
                                                        class="form-select form-select-sm" aria-label=".form-select-sm"
                                                        name="class_id" required>
                                                        @isset($school_classes)
                                                            <option selected disabled>Please select a class</option>
                                                            @foreach ($school_classes as $school_class)
                                                                <option value="{{$school_class->id}}">{{$school_class->class_name}}
                                                                </option>
                                                            @endforeach
                                                        @endisset
                                                    </select>
                                                </div>
                                                <div>
                                                    <p class="mt-2">Assign to section:<sup><i
                                                                class="bi bi-asterisk text-primary"></i></sup></p>
                                                    <select class="form-select form-select-sm" id="section-select"
                                                        aria-label=".form-select-sm" name="section_id" required>
                                                    </select>
                                                </div>
                                                <div>
                                                    <p class="mt-2">Assign to course:<sup><i
                                                                class="bi bi-asterisk text-primary"></i></sup></p>
                                                    <select class="form-select form-select-sm" id="course-select"
                                                        aria-label=".form-select-sm" name="course_id" required>
                                                    </select>
                                                </div>
                                                <button type="submit" class="mt-3 btn btn-sm btn-outline-primary"><i
                                                        class="bi bi-check2"></i> Save</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <div class="p-3 border bg-light shadow-sm">
                                            <h6>Allow Final Marks Submission</h6>
                                            <form action="{{route('school.final.marks.submission.status.update')}}"
                                                method="POST">
                                                @csrf
                                                <p class="text-danger">
                                                    <small><i class="bi bi-exclamation-diamond-fill me-2"></i> Usually teachers
                                                        are allowed to submit final marks just before the end of a
                                                        "Semester".</small>
                                                </p>
                                                <p class="text-primary">
                                                    <small><i class="bi bi-exclamation-diamond-fill me-2"></i> Disallow at the
                                                        start of a "Semester".</small>
                                                </p>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="marks_submission_status" id="marks_submission_status_check"
                                                        {{(optional($academic_setting)->marks_submission_status == 'on') ? 'checked="checked"' : null}}>
                                                    <label class="form-check-label"
                                                        for="marks_submission_status_check">{{(optional($academic_setting)->marks_submission_status == 'on') ? 'Allowed' : 'Disallowed'}}</label>
                                                </div>
                                                <button type="submit" class="mt-3 btn btn-sm btn-outline-primary"><i
                                                        class="bi bi-check2"></i> Save</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <div class="p-3 border bg-light shadow-sm">
                                            <h6>Financial Withholding</h6>
                                            <form action="{{route('school.financial.withholding.update')}}"
                                                method="POST">
                                                @csrf
                                                <p class="text-danger">
                                                    <small><i class="bi bi-exclamation-diamond-fill me-2"></i> When enabled, students with negative balances cannot view results or attendance.</small>
                                                </p>
                                                <p class="text-primary">
                                                    <small><i class="bi bi-info-circle-fill me-2"></i> Transparently bypasses for staff (Teachers, Admins, Accountants).</small>
                                                </p>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="enable_financial_withholding" id="enable_financial_withholding_check"
                                                        {{(optional($academic_setting)->enable_financial_withholding) ? 'checked="checked"' : null}}>
                                                    <label class="form-check-label"
                                                        for="enable_financial_withholding_check">{{(optional($academic_setting)->enable_financial_withholding) ? 'Enabled' : 'Disabled'}}</label>
                                                </div>
                                                <button type="submit" class="mt-3 btn btn-sm btn-outline-primary"><i
                                                        class="bi bi-check2"></i> Save</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <div class="p-3 border bg-light shadow-sm">
                                            <h6>Default Exam Weights</h6>
                                            <p class="text-primary">
                                                <small><i class="bi bi-info-circle-fill me-2"></i> Define the default percentage
                                                    weights for automatically created exams. The sum must be 100.</small>
                                            </p>
                                            <form action="{{route('school.default.weights.update')}}" method="POST">
                                                @csrf
                                                <div id="weights-container">
                                                    @if(optional($academic_setting)->marks_breakdown)
                                                        @foreach(optional($academic_setting)->marks_breakdown as $index => $item)
                                                            <div class="weight-row mb-2 border p-2 bg-white rounded shadow-sm">
                                                                <div class="row g-2 align-items-end">
                                                                    <div class="col-6">
                                                                        <label class="form-label small mb-1">Label (e.g. Exam)</label>
                                                                        <input type="text" name="names[]" class="form-control form-control-sm" value="{{$item['name']}}" required>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <label class="form-label small mb-1">Weight (%)</label>
                                                                        <input type="number" name="weights[]" class="form-control form-control-sm" value="{{$item['weight']}}" required min="0" max="100">
                                                                    </div>
                                                                    <div class="col-2 text-end">
                                                                        <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-weight-row"><i class="bi bi-trash"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="weight-row mb-2 border p-2 bg-white rounded shadow-sm">
                                                            <div class="row g-2 align-items-end">
                                                                <div class="col-6">
                                                                    <label class="form-label small mb-1">Label (e.g. Exam)</label>
                                                                    <input type="text" name="names[]" class="form-control form-control-sm" value="Final Exam" required>
                                                                </div>
                                                                <div class="col-4">
                                                                    <label class="form-label small mb-1">Weight (%)</label>
                                                                    <input type="number" name="weights[]" class="form-control form-control-sm" value="70" required min="0" max="100">
                                                                </div>
                                                                <div class="col-2 text-end">
                                                                    <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-weight-row"><i class="bi bi-trash"></i></button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="d-grid gap-2 mt-2">
                                                    <button type="button" id="add-weight-row" class="btn btn-sm btn-outline-secondary"><i class="bi bi-plus-lg"></i> Add Component</button>
                                                    <button type="submit" class="btn btn-sm btn-primary mt-2"><i class="bi bi-check2"></i> Save Weights</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <div class="p-3 border bg-light shadow-sm">
                                            <h6>Graduation Settings</h6>
                                            <p class="text-primary smaller">
                                                <small><i class="bi bi-info-circle-fill me-1"></i> Marks classes as "Final Year". Students in these classes will be evaluated for graduation in the Graduation Dashboard.</small>
                                            </p>
                                            <form action="{{route('school.final.grades.update')}}" method="POST">
                                                @csrf
                                                <div class="mb-3" style="max-height: 200px; overflow-y: auto;">
                                                    @foreach($school_classes as $class)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="final_grade_classes[]" value="{{$class->id}}" id="class_final_{{$class->id}}" {{$class->is_final_grade ? 'checked' : ''}}>
                                                        <label class="form-check-label small" for="class_final_{{$class->id}}">
                                                            {{$class->class_name}}
                                                        </label>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-check2"></i> Update Designations</button>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>
    <script>
        function getSectionsAndCourses(obj) {
            var class_id = obj.options[obj.selectedIndex].value;

            var url = "{{route('get.sections.courses.by.classId')}}?class_id=" + class_id

            fetch(url)
                .then((resp) => resp.json())
                .then(function (data) {
                    var sectionSelect = document.getElementById('section-select');
                    sectionSelect.options.length = 0;
                    data.sections.unshift({ 'id': 0, 'section_name': 'Please select a section' })
                    data.sections.forEach(function (section, key) {
                        sectionSelect[key] = new Option(section.section_name, section.id);
                    });

                    var courseSelect = document.getElementById('course-select');
                    courseSelect.options.length = 0;
                    data.courses.unshift({ 'id': 0, 'course_name': 'Please select a course' })
                    data.courses.forEach(function (course, key) {
                        courseSelect[key] = new Option(course.course_name, course.id);
                    });
                })
                .catch(function (error) {
                    console.log(error);
                });
        }

        document.getElementById('add-weight-row').addEventListener('click', function() {
            const container = document.getElementById('weights-container');
            const newRow = document.createElement('div');
            newRow.className = 'weight-row mb-2 border p-2 bg-white rounded shadow-sm';
            newRow.innerHTML = `
                <div class="row g-2 align-items-end">
                    <div class="col-6">
                        <label class="form-label small mb-1">Label (e.g. Exam)</label>
                        <input type="text" name="names[]" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label small mb-1">Weight (%)</label>
                        <input type="number" name="weights[]" class="form-control form-control-sm" required min="0" max="100">
                    </div>
                    <div class="col-2 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-weight-row"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            `;
            container.appendChild(newRow);
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-weight-row')) {
                const rows = document.querySelectorAll('.weight-row');
                if (rows.length > 1) {
                    e.target.closest('.weight-row').remove();
                } else {
                    alert('At least one component is required.');
                }
            }
        });
    </script>
@endsection
