@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-2">
                    <div class="col-md-8 ps-4">
                        @php $isEditing = isset($lessonPlan); @endphp
                        <h1 class="display-6 mb-3"><i class="bi {{ $isEditing ? 'bi-journal-check' : 'bi-journal-plus' }}"></i> {{ $isEditing ? 'Edit Lesson Plan' : 'Add Lesson Plan' }}</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('lesson-plans.index') }}">Lesson Plans</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{ $isEditing ? 'Edit' : 'Create' }}</li>
                            </ol>
                        </nav>
                        @include('session-messages')
                        <div class="p-3 border bg-light shadow-sm">
                            <form action="{{ $isEditing ? route('lesson-plans.update', $lessonPlan->id) : route('lesson-plans.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @if($isEditing)
                                    @method('PUT')
                                @endif
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control" value="{{ old('title', $lessonPlan->title ?? '') }}" required>
                                </div>

                                @if(Auth::user()->hasRole('Teacher'))
                                    <div class="mb-3">
                                        <label class="form-label">Assigned Course</label>
                                        <select class="form-select" id="assignment-select" required>
                                            <option value="">Please select</option>
                                            @foreach($assignments as $assignment)
                                                <option value="{{ $assignment->class_id }}|{{ $assignment->section_id }}|{{ $assignment->course_id }}"
                                                    data-class="{{ optional($assignment->schoolClass)->class_name }}"
                                                    data-section="{{ optional($assignment->section)->section_name }}"
                                                    data-course="{{ optional($assignment->course)->course_name }}"
                                                    {{ (old('class_id', $prefill['class_id'] ?? '') == $assignment->class_id && old('section_id', $prefill['section_id'] ?? '') == $assignment->section_id && old('course_id', $prefill['course_id'] ?? '') == $assignment->course_id) ? 'selected' : '' }}>
                                                    {{ optional($assignment->schoolClass)->class_name }} / {{ optional($assignment->section)->section_name }} / {{ optional($assignment->course)->course_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="class_id" id="class-id" value="{{ old('class_id', $prefill['class_id'] ?? '') }}">
                                        <input type="hidden" name="section_id" id="section-id" value="{{ old('section_id', $prefill['section_id'] ?? '') }}">
                                        <input type="hidden" name="course_id" id="course-id" value="{{ old('course_id', $prefill['course_id'] ?? '') }}">
                                    </div>
                                @else
                                    @if($isEditing)
                                        <div class="alert alert-info">
                                            Reviewing submission from <strong>{{ optional($lessonPlan->teacher)->first_name }} {{ optional($lessonPlan->teacher)->last_name }}</strong>.
                                        </div>
                                    @endif
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Class</label>
                                            <select onchange="getSectionsAndCourses(this.value)" class="form-select" name="class_id" id="class-id-admin" required>
                                                <option value="">Please select</option>
                                                @foreach($school_classes as $schoolClass)
                                                    <option value="{{ $schoolClass->id }}" {{ old('class_id', $prefill['class_id'] ?? '') == $schoolClass->id ? 'selected' : '' }}>{{ $schoolClass->class_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Section</label>
                                            <select class="form-select" name="section_id" id="section-id" required></select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Subject</label>
                                            <select class="form-select" name="course_id" id="course-id" required></select>
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label">Semester / Term</label>
                                    <select class="form-select" name="semester_id" required>
                                        <option value="">Please select</option>
                                        @foreach($semesters as $semester)
                                            <option value="{{ $semester->id }}" {{ old('semester_id', $prefill['semester_id'] ?? '') == $semester->id ? 'selected' : '' }}>
                                                {{ $semester->semester_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Lesson Plan Text</label>
                                    <textarea name="content" class="form-control" rows="8" placeholder="Type the lesson plan here if you are not uploading a file.">{{ old('content', $lessonPlan->content ?? '') }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Lesson Plan File (PDF, DOC, DOCX)</label>
                                    <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx">
                                    @if($isEditing && $lessonPlan->file_path)
                                        <div class="form-text">Current file: {{ $lessonPlan->file_name ?? basename($lessonPlan->file_path) }}</div>
                                    @endif
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check2"></i> {{ $isEditing ? 'Update Lesson Plan' : 'Save Lesson Plan' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>

    <script>
        const assignmentSelect = document.getElementById('assignment-select');
        if (assignmentSelect) {
            const syncAssignmentFields = () => {
                const value = assignmentSelect.value;
                if (!value) {
                    return;
                }

                const [classId, sectionId, courseId] = value.split('|');
                document.getElementById('class-id').value = classId;
                document.getElementById('section-id').value = sectionId;
                document.getElementById('course-id').value = courseId;
            };

            syncAssignmentFields();
            assignmentSelect.addEventListener('change', syncAssignmentFields);
        }

        function getSectionsAndCourses(classId) {
            const url = "{{ route('get.sections.courses.by.classId') }}?class_id=" + classId;
            fetch(url)
                .then((resp) => resp.json())
                .then((data) => {
                    const sectionSelect = document.getElementById('section-id');
                    const courseSelect = document.getElementById('course-id');
                    const selectedSectionId = "{{ old('section_id', $prefill['section_id'] ?? '') }}";
                    const selectedCourseId = "{{ old('course_id', $prefill['course_id'] ?? '') }}";

                    sectionSelect.options.length = 0;
                    courseSelect.options.length = 0;

                    data.sections.forEach((section) => {
                        const option = new Option(section.section_name, section.id, false, String(selectedSectionId) === String(section.id));
                        sectionSelect.add(option);
                    });

                    data.courses.forEach((course) => {
                        const option = new Option(course.course_name, course.id, false, String(selectedCourseId) === String(course.id));
                        courseSelect.add(option);
                    });
                });
        }

        const adminClassSelect = document.getElementById('class-id-admin');
        if (adminClassSelect && adminClassSelect.value) {
            getSectionsAndCourses(adminClassSelect.value);
        }
    </script>
@endsection
