@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/results-dashboard.css') }}">

<div class="container pb-5">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
            <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
                <h1 class="h3 mb-0 text-gray-800 fw-bold">Admin Results Audit</h1>
            </div>

            <!-- Search -->
            <div class="card shadow-sm border-0 mb-4 no-print">
                <div class="card-body">
                    <form action="{{ route('results.admin') }}" method="GET" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Search Student</label>
                            <select name="student_id" class="form-select border-light bg-light" onchange="this.form.submit()">
                                <option value="">-- Start typing student name... --</option>
                                @foreach($allStudents as $s)
                                    <option value="{{ $s->id }}" {{ (request('student_id') == $s->id) ? 'selected' : '' }}>
                                        {{ $s->first_name }} {{ $s->last_name }} ({{ $s->id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            @if($student)
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Consolidated Marks: {{ $student->first_name }} {{ $student->last_name }}</h6>
                        <span class="badge bg-light text-dark border shadow-sm px-3">Session 2026/2027</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="results-grid">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Course</th>
                                        @foreach($semesters as $semester)
                                            <th class="text-center">{{ $semester->semester_name }}</th>
                                        @endforeach
                                        <th class="text-center bg-light">Cumulative</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($courses as $course)
                                        <tr class="results-row">
                                            <td class="ps-4 fw-bold text-dark">{{ $course->course_name }}</td>
                                            @php $annual = 0; $count = 0; @endphp
                                            @foreach($semesters as $semester)
                                                @php
                                                    $mark = isset($results[$course->id]) ? $results[$course->id]->where('semester_id', $semester->id)->first() : null;
                                                    if($mark) { $annual += $mark->final_marks; $count++; }
                                                @endphp
                                                <td class="text-center">
                                                    @if($mark)
                                                        <span class="clickable-mark {{ $mark->final_marks < 50 ? 'text-danger' : 'text-success' }}"
                                                            data-student-id="{{ $student->id }}"
                                                            data-course-id="{{ $course->id }}"
                                                            data-semester-id="{{ $semester->id }}">
                                                            {{ number_format($mark->final_marks, 2) }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted opacity-25">--</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="text-center cumulative-total">{{ $count > 0 ? number_format($annual, 2) : '0.00' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-white py-3">
                         <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-chat-quote-fill me-2"></i>Report Comments Management</h6>
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
                                        <form action="{{ route('report.comments.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="student_id" value="{{ $student->id }}">
                                            <input type="hidden" name="semester_id" value="{{ $semester->id }}">
                                            <input type="hidden" name="type" value="teacher">
                                            
                                            <label class="form-label text-muted small fw-bold text-uppercase">Class Teacher's Remark</label>
                                            <div class="input-group">
                                                <textarea class="form-control" name="comment" rows="3">{{ $comment ? $comment->teacher_comment : '' }}</textarea>
                                                <button class="btn btn-outline-secondary" type="submit">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-md-6">
                                        <form action="{{ route('report.comments.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="student_id" value="{{ $student->id }}">
                                            <input type="hidden" name="semester_id" value="{{ $semester->id }}">
                                            <input type="hidden" name="type" value="principal">
                                            
                                            <label class="form-label text-muted small fw-bold text-uppercase">Principal's Remark</label>
                                            <div class="input-group">
                                                <textarea class="form-control" name="comment" rows="3">{{ $comment ? $comment->principal_comment : '' }}</textarea>
                                                <button class="btn btn-primary" type="submit">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                         @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-5 bg-white rounded shadow-sm border">
                    <i class="bi bi-search fs-1 text-muted opacity-25"></i>
                    <h5 class="mt-3 text-muted">Select a student to audit their full results history.</h5>
                </div>
            @endif

            @include('layouts.footer')
        </div>
    </div>
</div>

<!-- Breakdown Modal -->
<div class="modal fade" id="breakdownModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered shadow-lg">
        <div class="modal-content border-0">
            <div class="modal-header border-0 pb-0 shadow-sm">
                <h5 class="modal-title fw-bold">Auditor Breakdown</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4" id="breakdownContent">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="{{ asset('js/results-dashboard.js') }}"></script>
@endsection

