@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/results-dashboard.css') }}">

    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')

            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-3">
                    <div class="col ps-4">
                        <!-- Header -->
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h1 class="h3 mb-0 fw-bold text-dark">
                                <i class="bi bi-person-badge-fill text-primary me-2"></i>Section Performance Audit
                            </h1>
                        </div>

                        <div class="row g-4">
                            <!-- Selectors Sidebar -->
                            <div class="col-lg-4 no-print">
                                <!-- Section Selector -->
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-header bg-white border-bottom py-3">
                                        <h6 class="mb-0 fw-semibold text-dark">
                                            <i class="bi bi-1-circle text-primary me-2"></i>Select Section
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('results.section') }}" method="GET" id="sectionForm">
                                            <select name="section_id"
                                                class="form-select form-select-lg border-light bg-light"
                                                onchange="this.form.submit()">
                                                <option value="">-- Choose Section --</option>
                                                @foreach($sections as $s)
                                                    <option value="{{ $s->section_id }}" {{ $section_id == $s->section_id ? 'selected' : '' }}>
                                                        {{ $s->schoolClass->class_name ?? '??' }}
                                                        ({{ $s->section->section_name ?? '??' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </div>
                                </div>

                                <!-- Student List -->
                                @if($section_id && count($students) > 0)
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-white border-bottom py-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0 fw-semibold text-dark">
                                                    <i class="bi bi-2-circle text-primary me-2"></i>Select Student
                                                </h6>
                                                <span class="badge bg-primary rounded-pill">{{ count($students) }}</span>
                                            </div>
                                        </div>
                                        <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                                            <div class="list-group list-group-flush">
                                                @foreach($students as $s)
                                                    <a href="{{ route('results.section', ['section_id' => $section_id, 'student_id' => $s->id]) }}"
                                                        class="list-group-item list-group-item-action border-0 py-3 {{ $student_id == $s->id ? 'active' : '' }}">
                                                        <div class="d-flex align-items-center">
                                                            <div
                                                                class="bg-{{ $student_id == $s->id ? 'white' : 'primary' }} bg-opacity-10 p-2 rounded me-3">
                                                                <i
                                                                    class="bi bi-person-fill {{ $student_id == $s->id ? 'text-primary' : 'text-primary' }}"></i>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <div class="fw-semibold">{{ $s->first_name }} {{ $s->last_name }}
                                                                </div>
                                                                <small
                                                                    class="{{ $student_id == $s->id ? 'text-white-50' : 'text-muted' }}">ID:
                                                                    {{ $s->id }}</small>
                                                            </div>
                                                        </div>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @elseif($section_id)
                                    <div class="alert alert-info border-0 shadow-sm">
                                        <i class="bi bi-info-circle me-2"></i>No students found in this section.
                                    </div>
                                @endif
                            </div>

                            <!-- Results Display -->
                            <div class="col-lg-8">
                                @if($selectedStudent)
                                    <!-- Student Info Card -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary bg-opacity-10 p-3 rounded me-3">
                                                        <i class="bi bi-person-circle text-primary fs-3"></i>
                                                    </div>
                                                    <div>
                                                        <h5 class="mb-0 fw-bold">{{ $selectedStudent->first_name }}
                                                            {{ $selectedStudent->last_name }}</h5>
                                                        <p class="text-muted small mb-0">Student ID: {{ $selectedStudent->id }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <button onclick="window.print()"
                                                    class="btn btn-outline-primary shadow-sm no-print px-4">
                                                    <i class="bi bi-printer me-2"></i>Print
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Performance Summary -->
                                    <div class="row g-3 mb-4">
                                        @php
                                            $totalMarks = 0;
                                            $totalCount = 0;
                                            $passCount = 0;
                                            $failCount = 0;

                                            foreach ($results as $courseResults) {
                                                foreach ($courseResults as $mark) {
                                                    $totalMarks += $mark->final_marks;
                                                    $totalCount++;
                                                    if ($mark->final_marks >= 50) {
                                                        $passCount++;
                                                    } else {
                                                        $failCount++;
                                                    }
                                                }
                                            }
                                            $studentAvg = $totalCount > 0 ? ($totalMarks / $totalCount) : 0;
                                        @endphp

                                        <div class="col-md-4">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <p class="text-muted text-uppercase small mb-1">Average Score</p>
                                                            <h3
                                                                class="mb-0 fw-bold {{ $studentAvg >= 70 ? 'text-success' : ($studentAvg >= 50 ? 'text-warning' : 'text-danger') }}">
                                                                {{ number_format($studentAvg, 1) }}%
                                                            </h3>
                                                        </div>
                                                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                                                            <i class="bi bi-trophy-fill text-primary fs-4"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <p class="text-muted text-uppercase small mb-1">Courses Passed</p>
                                                            <h3 class="mb-0 fw-bold text-success">{{ $passCount }}</h3>
                                                        </div>
                                                        <div class="bg-success bg-opacity-10 p-3 rounded">
                                                            <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <p class="text-muted text-uppercase small mb-1">Courses Failed</p>
                                                            <h3 class="mb-0 fw-bold text-danger">{{ $failCount }}</h3>
                                                        </div>
                                                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                                                            <i class="bi bi-x-circle-fill text-danger fs-4"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Results Table -->
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-white border-bottom py-3">
                                            <h5 class="mb-0 fw-bold text-dark">
                                                <i class="bi bi-table me-2 text-primary"></i>Consolidated Results
                                            </h5>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th class="ps-4 py-3 fw-semibold">Course</th>
                                                            @foreach($semesters as $semester)
                                                                <th class="text-center py-3 fw-semibold">
                                                                    {{ $semester->semester_name }}</th>
                                                            @endforeach
                                                            <th class="text-center py-3 fw-semibold bg-light">Annual Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($courses as $course)
                                                            <tr class="border-bottom">
                                                                <td class="ps-4 py-3">
                                                                    <div class="fw-semibold text-dark">{{ $course->course_name }}
                                                                    </div>
                                                                    <div class="text-muted small">{{ $course->course_type }}</div>
                                                                </td>
                                                                @php $annual = 0;
                                                                $count = 0; @endphp
                                                                @foreach($semesters as $semester)
                                                                    @php
                                                                        $mark = isset($results[$course->id]) ? $results[$course->id]->where('semester_id', $semester->id)->first() : null;
                                                                        if ($mark) {
                                                                            $annual += $mark->final_marks;
                                                                            $count++;
                                                                        }
                                                                    @endphp
                                                                    <td class="text-center py-3">
                                                                        @if($mark)
                                                                            @php
                                                                                $colorClass = $mark->final_marks >= 80 ? 'success' : ($mark->final_marks >= 60 ? 'primary' : ($mark->final_marks >= 50 ? 'warning' : 'danger'));
                                                                            @endphp
                                                                            <span
                                                                                class="badge bg-{{ $colorClass }} bg-opacity-10 text-{{ $colorClass }} px-3 py-2 fw-semibold clickable-mark"
                                                                                style="cursor: pointer; font-size: 0.95rem;"
                                                                                data-student-id="{{ $selectedStudent->id }}"
                                                                                data-course-id="{{ $course->id }}"
                                                                                data-semester-id="{{ $semester->id }}">
                                                                                {{ number_format($mark->final_marks, 1) }}
                                                                            </span>
                                                                        @else
                                                                            <span class="text-muted">—</span>
                                                                        @endif
                                                                    </td>
                                                                @endforeach
                                                                <td class="text-center py-3 bg-light">
                                                                    <span
                                                                        class="fw-bold text-dark">{{ $count > 0 ? number_format($annual, 1) : '0.0' }}</span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Remarks Section -->
                                    <div class="card border-0 shadow-sm mt-4">
                                        <div class="card-header bg-white border-bottom py-3">
                                            <h5 class="mb-0 fw-bold text-dark">
                                                <i class="bi bi-chat-quote me-2 text-primary"></i>Term Remarks & Comments
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            @foreach($semesters as $semester)
                                                @php
                                                    $comment = isset($comments) ? $comments->get($semester->id) : null;
                                                @endphp
                                                <div class="mb-4 {{ !$loop->last ? 'border-bottom pb-4' : '' }}">
                                                    <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-calendar-event me-2"></i>{{ $semester->semester_name }}</h6>
                                                    
                                                    <form action="{{ route('report.comments.store') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="student_id" value="{{ $selectedStudent->id }}">
                                                        <input type="hidden" name="semester_id" value="{{ $semester->id }}">
                                                        <input type="hidden" name="type" value="teacher">
                                                        
                                                        <label class="form-label text-muted small fw-bold text-uppercase">Class Teacher's Remark</label>
                                                        <div class="input-group">
                                                            <textarea class="form-control" name="comment" rows="2" placeholder="Enter remark...">{{ $comment ? $comment->teacher_comment : '' }}</textarea>
                                                            <button class="btn btn-outline-primary" type="submit"><i class="bi bi-save me-1"></i>Save</button>
                                                        </div>
                                                        @if($comment && $comment->principal_comment)
                                                            <div class="mt-3 p-3 bg-light rounded border-start border-4 border-dark">
                                                                <p class="mb-1 text-muted small fw-bold text-uppercase">Principal's Remark</p>
                                                                <p class="mb-0 text-dark fst-italic">"{{ $comment->principal_comment }}"</p>
                                                            </div>
                                                        @endif
                                                    </form>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <!-- Empty State -->
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body text-center py-5">
                                            <i class="bi bi-person-bounding-box fs-1 text-muted opacity-25"></i>
                                            <h5 class="mt-3 text-muted">No Student Selected</h5>
                                            <p class="text-muted">Select a section and student from the sidebar to view detailed
                                                results.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @include('layouts.footer')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Breakdown Modal -->
    <div class="modal fade" id="breakdownModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-light">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-bar-chart-fill text-primary me-2"></i>Assessment Breakdown
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4" id="breakdownContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-3">Loading breakdown...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/results-dashboard.js') }}"></script>
@endsection