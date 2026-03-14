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
                <div>
                    <h1 class="h3 mb-1 fw-bold text-dark">
                        <i class="bi bi-journal-check text-primary me-2"></i>Course Results Dashboard
                    </h1>
                    <p class="text-muted small mb-0">Track student performance across terms and assessments</p>
                </div>
                <div class="no-print">
                    <button onclick="window.print()" class="btn btn-outline-primary shadow-sm px-4">
                        <i class="bi bi-printer me-2"></i>Print Report
                    </button>
                </div>
            </div>

            <!-- Course Selection Filter -->
            <div class="card border-0 shadow-sm mb-4 no-print">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold text-dark">
                        <i class="bi bi-funnel text-primary me-2"></i>Select Course & Class
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('results.teacher') }}" method="GET">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-10">
                                <label class="form-label small fw-semibold text-muted">Course Selection</label>
                                <select name="course_class" class="form-select form-select-lg border-light bg-light" onchange="this.form.submit()">
                                    <option value="">-- Select Course & Class --</option>
                                    @foreach($assignments as $a)
                                        @php $val = $a->course_id . '|' . $a->class_id . '|' . $a->section_id; @endphp
                                        <option value="{{ $val }}" {{ (request('course_class') == $val) ? 'selected' : '' }}>
                                            {{ $a->course->course_name ?? 'Unknown Course' }} • {{ $a->schoolClass->class_name ?? 'Unknown Class' }} ({{ $a->section->section_name ?? '??' }})
                                        </option>
                                    @endforeach
                                </select>
                                @if(request('course_class'))
                                    @php 
                                        $parts = explode('|', request('course_class'));
                                        echo '<input type="hidden" name="course_id" value="'.($parts[0] ?? '').'">';
                                        echo '<input type="hidden" name="class_id" value="'.($parts[1] ?? '').'">';
                                        echo '<input type="hidden" name="section_id" value="'.($parts[2] ?? '').'">';
                                    @endphp
                                @endif
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Load
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(count($students) > 0)
            <!-- Summary Stats -->
            <div class="row g-3 mb-4">
                @php
                    $totalStudents = count($students);
                    $passCount = 0;
                    $failCount = 0;
                    $totalAvg = 0;
                    $avgCount = 0;
                    
                    foreach($students as $student) {
                        if(isset($results[$student->id])) {
                            foreach($results[$student->id] as $mark) {
                                $totalAvg += $mark->final_marks;
                                $avgCount++;
                                if($mark->final_marks >= 50) {
                                    $passCount++;
                                } else {
                                    $failCount++;
                                }
                            }
                        }
                    }
                    $classAvg = $avgCount > 0 ? ($totalAvg / $avgCount) : 0;
                @endphp

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted text-uppercase small mb-1">Total Students</p>
                                    <h3 class="mb-0 fw-bold">{{ $totalStudents }}</h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-people-fill text-primary fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted text-uppercase small mb-1">Class Average</p>
                                    <h3 class="mb-0 fw-bold {{ $classAvg >= 70 ? 'text-success' : ($classAvg >= 50 ? 'text-warning' : 'text-danger') }}">
                                        {{ number_format($classAvg, 1) }}%
                                    </h3>
                                </div>
                                <div class="bg-info bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-graph-up text-info fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted text-uppercase small mb-1">Pass Rate</p>
                                    <h3 class="mb-0 fw-bold text-success">
                                        {{ $avgCount > 0 ? number_format(($passCount / $avgCount) * 100, 1) : 0 }}%
                                    </h3>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted text-uppercase small mb-1">Fail Rate</p>
                                    <h3 class="mb-0 fw-bold text-danger">
                                        {{ $avgCount > 0 ? number_format(($failCount / $avgCount) * 100, 1) : 0 }}%
                                    </h3>
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
                        <i class="bi bi-table me-2 text-primary"></i>Student Performance Matrix
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 fw-semibold">Student Name</th>
                                    @foreach($semesters as $semester)
                                        <th class="text-center py-3 fw-semibold">{{ $semester->semester_name }}</th>
                                    @endforeach
                                    <th class="text-center py-3 fw-semibold bg-light">Annual Total</th>
                                    <th class="text-center py-3 fw-semibold bg-light">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
                                    <tr class="border-bottom">
                                        <td class="ps-4 py-3">
                                            <div class="fw-semibold text-dark">{{ $student->first_name }} {{ $student->last_name }}</div>
                                            <div class="text-muted small">ID: {{ $student->id }}</div>
                                        </td>

                                        @php $annualTotal = 0; $termsWithMarks = 0; @endphp

                                        @foreach($semesters as $semester)
                                            @php
                                                $mark = isset($results[$student->id]) ? $results[$student->id]->where('semester_id', $semester->id)->first() : null;
                                                $score = $mark ? $mark->final_marks : null;
                                                if ($score !== null) {
                                                    $annualTotal += $score;
                                                    $termsWithMarks++;
                                                }
                                            @endphp
                                            <td class="text-center py-3">
                                                @if($score !== null)
                                                    @php
                                                        $colorClass = $score >= 80 ? 'success' : ($score >= 60 ? 'primary' : ($score >= 50 ? 'warning' : 'danger'));
                                                    @endphp
                                                    <span class="badge bg-{{ $colorClass }} bg-opacity-10 text-{{ $colorClass }} px-3 py-2 fw-semibold clickable-mark" 
                                                        style="cursor: pointer; font-size: 0.95rem;"
                                                        data-student-id="{{ $student->id }}" 
                                                        data-course-id="{{ $course_id }}"
                                                        data-semester-id="{{ $semester->id }}">
                                                        {{ number_format($score, 1) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        @endforeach

                                        <td class="text-center py-3 bg-light">
                                            <span class="fw-bold text-dark">
                                                {{ $termsWithMarks > 0 ? number_format($annualTotal, 1) : '0.0' }}
                                            </span>
                                        </td>
                                        <td class="text-center py-3 bg-light">
                                            @php
                                                $avg = $termsWithMarks > 0 ? ($annualTotal / $termsWithMarks) : 0;
                                            @endphp
                                            @if($termsWithMarks > 0)
                                                <span class="badge {{ $avg >= 50 ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                                                    {{ $avg >= 50 ? 'PASS' : 'FAIL' }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary px-3 py-2">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <!-- Empty State -->
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted opacity-25"></i>
                    <h5 class="mt-3 text-muted">No Course Selected</h5>
                    <p class="text-muted">Please select a course and class from the dropdown above to view student results.</p>
                </div>
            </div>
            @endif

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
                    <i class="bi bi-bar-chart-fill text-primary me-2"></i>Performance Breakdown
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
