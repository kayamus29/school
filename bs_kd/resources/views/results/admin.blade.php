@extends('layouts.app')

@section('content')
    @include('results.partials.report-theme')

    @php
        $affectiveLabels = [
            'punctuality' => 'Punctuality',
            'neatness' => 'Neatness',
            'politeness' => 'Politeness',
            'honesty' => 'Honesty',
            'performance' => 'Performance',
            'attentiveness' => 'Attentiveness',
            'perseverance' => 'Perseverance',
            'speaking' => 'Speaking',
            'writing' => 'Writing',
        ];

        $gradeBadge = function ($score) {
            if ($score >= 80) return ['A', 'report-grade-a', 'Outstanding', 'var(--report-green)'];
            if ($score >= 70) return ['B', 'report-grade-b', 'Very Good', '#2a5abc'];
            if ($score >= 60) return ['C', 'report-grade-c', 'Good', '#9b6b00'];
            if ($score >= 50) return ['D', 'report-grade-d', 'Average', 'var(--report-amber)'];
            return ['F', 'report-grade-f', 'Below Pass', 'var(--report-red)'];
        };
    @endphp

    <div class="container report-view">
        <div class="row justify-content-start">
            @include('layouts.left-menu')

            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 no-print">
                    <h1 class="h3 mb-0 text-gray-800 fw-bold">Admin Results Audit</h1>
                </div>

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
                    @php
                        $studentInitials = strtoupper(substr($student->first_name ?? 'S', 0, 1) . substr($student->last_name ?? 'R', 0, 1));
                        $schoolLogoPath = $site_setting->school_logo_path ?? null;
                        $schoolLogo = !empty($schoolLogoPath)
                            ? (\Illuminate\Support\Str::startsWith($schoolLogoPath, ['http://', 'https://', '/']) ? $schoolLogoPath : asset($schoolLogoPath))
                            : null;
                        $schoolName = $site_setting->school_name ?? config('app.name');
                        $schoolContact = collect([
                            $site_setting->school_address ?? null,
                            $site_setting->school_phone ?? null,
                            $site_setting->school_email ?? null,
                        ])->filter()->implode(' | ');
                        $sessionName = optional(optional($promotion)->session)->session_name ?? 'Current Session';
                        $className = optional(optional($promotion)->schoolClass)->class_name ?? 'Not Assigned';
                        $sectionName = optional(optional($promotion)->section)->section_name ?? null;
                        $gender = ucfirst($student->gender ?? 'N/A');
                        $birthday = !empty($student->birthday) ? \Carbon\Carbon::parse($student->birthday)->format('d M Y') : 'N/A';

                        $flatScores = collect($results ?? [])->flatMap(function ($courseResults) {
                            return collect($courseResults)->pluck('final_marks');
                        })->filter(fn($score) => $score !== null);

                        $overallAverage = $flatScores->count() ? round($flatScores->avg(), 1) : 0;
                        $passCount = $flatScores->filter(fn($score) => $score >= 50)->count();
                        $failCount = $flatScores->filter(fn($score) => $score < 50)->count();
                    @endphp

                    <div class="report-toolbar report-hidden-print">
                        <button onclick="window.print()" class="btn btn-outline-primary shadow-sm px-4">
                            <i class="bi bi-printer me-2"></i>Print Report
                        </button>
                    </div>

                    <div class="report-page">
                        <div class="report-header">
                            <div class="report-logo-wrap">
                                @if($schoolLogo)
                                    <img src="{{ $schoolLogo }}" alt="{{ $schoolName }}">
                                @else
                                    {{ strtoupper(substr($schoolName, 0, 3)) }}
                                @endif
                            </div>
                            <div class="report-school-info">
                                <div class="report-school-name">{{ $schoolName }}</div>
                                <div class="report-school-tagline">Administrative Audit Copy</div>
                                <div class="report-school-contact">
                                    {{ $schoolContact ?: 'Full student report overview for administrative review and comment management.' }}
                                </div>
                            </div>
                            <div class="report-badge-box">
                                <span class="report-badge-label">Admin Audit</span>
                                <span class="report-badge-term">Session Overview</span>
                                <span class="report-badge-session">{{ $sessionName }}</span>
                            </div>
                        </div>

                        <div class="report-gold-rule"></div>

                        <div class="report-student-banner">
                            <div class="report-avatar">{{ $studentInitials }}</div>
                            <div class="report-student-details">
                                <div class="report-student-name">{{ $student->first_name }} {{ $student->last_name }}</div>
                                <div class="report-student-meta">
                                    <span>Student ID: <strong>{{ optional($promotion)->id_card_number ?? $student->id }}</strong></span>
                                    <span>Class: <strong>{{ $className }}{{ $sectionName ? ' (' . $sectionName . ')' : '' }}</strong></span>
                                    <span>Gender: <strong>{{ $gender }}</strong></span>
                                    <span>Date of Birth: <strong>{{ $birthday }}</strong></span>
                                </div>
                            </div>
                            <div class="report-summary-pills">
                                <div class="report-pill avg">
                                    <span class="report-pill-value">{{ number_format($overallAverage, 1) }}%</span>
                                    <span class="report-pill-label">Average</span>
                                </div>
                                <div class="report-pill pass">
                                    <span class="report-pill-value">{{ $passCount }}</span>
                                    <span class="report-pill-label">Passed</span>
                                </div>
                                <div class="report-pill fail">
                                    <span class="report-pill-value">{{ $failCount }}</span>
                                    <span class="report-pill-label">Failed</span>
                                </div>
                            </div>
                        </div>

                        <div class="report-body">
                            <div class="report-section-title">Academic Performance</div>
                            <div class="report-table-wrap mb-4">
                                <div class="table-responsive">
                                    <table class="report-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 28%;">Subject</th>
                                                @foreach($semesters as $semester)
                                                    <th class="text-center">{{ $semester->semester_name }}</th>
                                                @endforeach
                                                <th class="text-center">Average</th>
                                                <th class="text-center" style="width: 14%;">Progress</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($courses as $course)
                                                @php
                                                    $courseMarks = collect($results[$course->id] ?? []);
                                                    $scores = [];
                                                    foreach ($semesters as $semester) {
                                                        $scores[$semester->id] = optional($courseMarks->where('semester_id', $semester->id)->first())->final_marks;
                                                    }
                                                    $existingScores = collect($scores)->filter(fn($score) => $score !== null);
                                                    $courseAverage = $existingScores->count() ? round($existingScores->avg(), 1) : null;
                                                    [$letterGrade, $gradeClass, $remark, $progressColor] = $gradeBadge($courseAverage ?? 0);
                                                    $tagClass = in_array(strtolower($course->course_type), ['core']) ? 'report-tag-core' : 'report-tag-elective';
                                                @endphp
                                                <tr>
                                                    <td>
                                                        {{ $course->course_name }}
                                                        <span class="report-tag {{ $tagClass }}">{{ $course->course_type }}</span>
                                                    </td>
                                                    @foreach($semesters as $semester)
                                                        @php
                                                            $mark = $courseMarks->where('semester_id', $semester->id)->first();
                                                            $score = optional($mark)->final_marks;
                                                        @endphp
                                                        <td class="text-center">
                                                            @if($score !== null)
                                                                <span class="report-score-cell clickable-mark"
                                                                    style="cursor:pointer;"
                                                                    data-student-id="{{ $student->id }}"
                                                                    data-course-id="{{ $course->id }}"
                                                                    data-semester-id="{{ $semester->id }}">
                                                                    {{ number_format($score, 1) }}
                                                                </span>
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                    <td class="text-center">
                                                        @if($courseAverage !== null)
                                                            <span class="report-score-cell">{{ number_format($courseAverage, 1) }}</span>
                                                            <span class="report-grade-badge {{ $gradeClass }} ms-2">{{ $letterGrade }}</span>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($courseAverage !== null)
                                                            <div class="report-progress">
                                                                <div class="report-progress-fill" style="width: {{ max(0, min(100, $courseAverage)) }}%; background: {{ $progressColor }}"></div>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            @foreach($semesters as $semester)
                                @php
                                    $comment = isset($comments) ? $comments->get($semester->id) : null;
                                    $attendanceSummary = isset($attendanceSummaries) ? $attendanceSummaries->get($semester->id) : null;
                                    $endTermUpdate = isset($endTermUpdates) ? $endTermUpdates->get($semester->id) : null;
                                    $affective = $comment && is_array($comment->affective_scores) ? $comment->affective_scores : [];
                                    $daysAbsent = $attendanceSummary ? max(0, (int) $attendanceSummary['total_school_days'] - (int) $attendanceSummary['days_present']) : null;
                                @endphp
                                <div class="report-block report-term-card">
                                    <div class="report-term-title">{{ $semester->semester_name }}</div>

                                    @if($attendanceSummary)
                                        <div class="report-section-title">Attendance Record</div>
                                        <div class="report-attendance-row">
                                            <div class="report-att-box">
                                                <span class="report-att-num">{{ $attendanceSummary['total_school_days'] }}</span>
                                                <span class="report-att-lbl">School Days</span>
                                            </div>
                                            <div class="report-att-box">
                                                <span class="report-att-num" style="color: var(--report-green)">{{ $attendanceSummary['days_present'] }}</span>
                                                <span class="report-att-lbl">Days Present</span>
                                            </div>
                                            <div class="report-att-box">
                                                <span class="report-att-num" style="color: var(--report-red)">{{ $daysAbsent }}</span>
                                                <span class="report-att-lbl">Days Absent</span>
                                            </div>
                                            <div class="report-att-box">
                                                <span class="report-att-num" style="color: var(--report-gold)">{{ $attendanceSummary['attendance_percentage'] !== null ? $attendanceSummary['attendance_percentage'] . '%' : 'N/A' }}</span>
                                                <span class="report-att-lbl">Attendance Rate</span>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="report-section-title">Psychomotor & Affective Skills</div>
                                    <div class="report-psycho-grid">
                                        @foreach($affectiveLabels as $key => $label)
                                            @php $score = (int) ($affective[$key] ?? 0); @endphp
                                            <div class="report-psycho-item">
                                                <div class="report-psycho-label">{{ $label }}</div>
                                                <div class="report-stars">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <span class="{{ $i <= $score ? '' : 'empty' }}">★</span>
                                                    @endfor
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="report-rating-key">
                                        <span>1 – Poor</span>
                                        <span>2 – Fair</span>
                                        <span>3 – Good</span>
                                        <span>4 – Very Good</span>
                                        <span>5 – Excellent</span>
                                    </div>

                                    <div class="report-section-title">Remarks & Comments</div>
                                    <div class="report-comments-grid mb-3">
                                        <div class="report-comment-box">
                                            <span class="report-comment-label">Class Teacher's Remark</span>
                                            <p>{{ $comment && $comment->teacher_comment ? $comment->teacher_comment : 'No remark yet.' }}</p>
                                        </div>
                                        <div class="report-comment-box">
                                            <span class="report-comment-label">Principal's Remark</span>
                                            <p>{{ $comment && $comment->principal_comment ? $comment->principal_comment : 'No remark yet.' }}</p>
                                        </div>
                                    </div>

                                    @include('results.partials.end-term-update', ['endTermUpdate' => $endTermUpdate, 'semester' => $semester])

                                    <div class="row g-3 no-print">
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

                        <div class="report-footer">
                            <div class="report-footer-left">
                                {{ $schoolName }}<br>
                                Generated on {{ now()->format('d F Y, h:i A') }}. Administrative copy.
                            </div>
                            <div class="report-footer-stamp">Audit Copy</div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5 bg-white rounded shadow-sm border">
                        <i class="bi bi-search fs-1 text-muted opacity-25"></i>
                        <h5 class="mt-3 text-muted">Select a student to audit the full report card.</h5>
                    </div>
                @endif

                @include('layouts.footer')
            </div>
        </div>
    </div>

    <div class="modal fade" id="breakdownModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered shadow-lg">
            <div class="modal-content border-0">
                <div class="modal-header border-0 pb-0 shadow-sm">
                    <h5 class="modal-title fw-bold">Auditor Breakdown</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4" id="breakdownContent"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/results-dashboard.js') }}"></script>
@endsection
