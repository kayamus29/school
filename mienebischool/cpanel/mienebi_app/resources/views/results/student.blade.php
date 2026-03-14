@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/results-dashboard.css') }}">

    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')

            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-3">
                    <div class="col ps-4">
                <!-- Header Section -->
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h1 class="h3 mb-1 fw-bold text-dark">
                            <i class="bi bi-graph-up-arrow text-primary me-2"></i>Academic Performance
                        </h1>
                        <p class="text-muted small mb-0">
                            @if(isset($promotion))
                                {{ $promotion->schoolClass->class_name }} • {{ $promotion->session->session_name }}
                            @else
                                {{ $session_id }} <!-- Fallback if needed, or just empty -->
                            @endif
                        </p>
                    </div>
                    <div class="no-print">
                        <button onclick="window.print()" class="btn btn-outline-primary shadow-sm px-4" {{ isset($withheld) && $withheld ? 'disabled' : '' }}>
                            <i class="bi bi-printer me-2"></i>Print Transcript
                        </button>
                    </div>
                </div>

                @if(isset($withheld) && $withheld)
                <!-- Financial Withholding Alert -->
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <div class="d-flex align-items-start">
                        <div class="bg-white bg-opacity-25 p-3 rounded me-3">
                            <i class="bi bi-exclamation-octagon-fill fs-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="alert-heading fw-bold mb-2">Academic Records Withheld</h4>
                            <p class="mb-2">Your academic performance records are temporarily restricted due to an outstanding financial balance.</p>
                            <hr class="my-3 border-white border-opacity-25">
                            <p class="mb-0 small">
                                <i class="bi bi-info-circle me-1"></i>
                                Please visit the Bursar's Office or settle your account balance to regain full access to your academic records.
                            </p>
                        </div>
                    </div>
                </div>
                @else
                <!-- Summary Statistics -->
                <div class="row g-3 mb-4">
                    @php
                        $grandTotal = 0;
                        $totalEntries = 0;
                        $passCount = 0;
                        $failCount = 0;
                        
                        foreach ($results as $cResults) {
                            foreach ($cResults as $mark) {
                                $grandTotal += $mark->final_marks;
                                $totalEntries++;
                                if ($mark->final_marks >= 50) {
                                    $passCount++;
                                } else {
                                    $failCount++;
                                }
                            }
                        }
                        $totalAvg = $totalEntries > 0 ? ($grandTotal / $totalEntries) : 0;
                    @endphp

                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted text-uppercase small mb-1">Overall Average</p>
                                        <h3 class="mb-0 fw-bold {{ $totalAvg >= 70 ? 'text-success' : ($totalAvg >= 50 ? 'text-warning' : 'text-danger') }}">
                                            {{ number_format($totalAvg, 1) }}%
                                        </h3>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                                        <i class="bi bi-trophy-fill text-primary fs-4"></i>
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

                    <div class="col-md-3">
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

                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted text-uppercase small mb-1">Total Courses</p>
                                        <h3 class="mb-0 fw-bold">{{ count($courses) }}</h3>
                                    </div>
                                    <div class="bg-info bg-opacity-10 p-3 rounded">
                                        <i class="bi bi-journal-text text-info fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Alert -->
                <div class="alert alert-info border-0 shadow-sm mb-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill me-3 fs-5"></i>
                        <div>
                            <strong>Interactive Results:</strong> Click on any score to view detailed assessment breakdown including Continuous Assessment (CA) and Exam components.
                        </div>
                    </div>
                </div>

                <!-- Performance Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="bi bi-table me-2 text-primary"></i>Course Performance Summary
                            </h5>
                            <span class="badge bg-light text-dark">{{ count($courses) }} Courses</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 fw-semibold">Course</th>
                                        @foreach($semesters as $semester)
                                            <th class="text-center py-3 fw-semibold">{{ $semester->semester_name }}</th>
                                        @endforeach
                                        <th class="text-center py-3 fw-semibold bg-light">Annual Total</th>
                                        <th class="text-center py-3 fw-semibold bg-light">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($courses as $course)
                                        <tr class="border-bottom">
                                            <td class="ps-4 py-3">
                                                <div class="fw-semibold text-dark">{{ $course->course_name }}</div>
                                                <div class="text-muted small">{{ $course->course_type }}</div>
                                            </td>

                                            @php 
                                                $annualTotal = 0;
                                                $termsWithMarks = 0; 
                                            @endphp

                                            @foreach($semesters as $semester)
                                                @php
                                                    $mark = isset($results[$course->id]) ? $results[$course->id]->where('semester_id', $semester->id)->first() : null;
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
                                                            data-course-id="{{ $course->id }}"
                                                            data-semester-id="{{ $semester->id }}">
                                                            {{ number_format($score, 1) }}
                                                        </span>
                                                        @if(isset($mark->is_provisional) && $mark->is_provisional)
                                                            <span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;">Provisional</span>
                                                        @endif
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

                @endif

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
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="h-100 p-3 bg-light bg-opacity-50 rounded border">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="bi bi-person-badge text-primary me-2"></i>
                                                <span class="text-uppercase small fw-bold text-muted">Class Teacher's Remark</span>
                                            </div>
                                            @if($comment && $comment->teacher_comment)
                                                <p class="mb-0 text-dark fst-italic">"{{ $comment->teacher_comment }}"</p>
                                            @else
                                                <p class="mb-0 text-muted small fst-italic">No remark yet.</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="h-100 p-3 bg-light bg-opacity-50 rounded border">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="bi bi-person-check text-dark me-2"></i>
                                                <span class="text-uppercase small fw-bold text-muted">Principal's Remark</span>
                                            </div>
                                            @if($comment && $comment->principal_comment)
                                                <p class="mb-0 text-dark fst-italic">"{{ $comment->principal_comment }}"</p>
                                            @else
                                                <p class="mb-0 text-muted small fst-italic">No remark yet.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
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
