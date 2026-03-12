@extends('layouts.app')

@section('content')
    @include('results.partials.report-theme')

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
                <div class="row pt-3">
                    <div class="col ps-4">
                        @if(isset($withheld) && $withheld)
                            <div class="alert alert-danger border-0 shadow-sm mb-4">
                                <h4 class="fw-bold mb-2">Academic Records Withheld</h4>
                                <p class="mb-0">Please settle the outstanding financial balance to view this report card.</p>
                            </div>
                        @elseif(isset($error))
                            <div class="alert alert-warning border-0 shadow-sm mb-4">
                                <p class="mb-0">{{ $error }}</p>
                            </div>
                        @else
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
                                        <div class="report-school-tagline">Academic Excellence Report</div>
                                        <div class="report-school-contact">
                                            {{ $schoolContact ?: 'Student portal report card for the active academic session.' }}
                                        </div>
                                    </div>
                                    <div class="report-badge-box">
                                        <span class="report-badge-label">Student Report</span>
                                        <span class="report-badge-term">Session Overview</span>
                                        <span class="report-badge-session">{{ $sessionName }}</span>
                                    </div>
                                </div>

                                <div class="report-gold-rule"></div>

                                <div class="report-student-banner">
                                    <div class="report-avatar">
                                        @if(!empty($student->photo))
                                            <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->first_name }}">
                                        @else
                                            {{ $studentInitials }}
                                        @endif
                                    </div>
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
                                    <div class="report-info-banner mb-4">
                                        Click any score to view the detailed assessment breakdown for that subject and term.
                                    </div>
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
                                                            $termScores = [];
                                                            foreach ($semesters as $semester) {
                                                                $termScores[$semester->id] = optional($courseMarks->where('semester_id', $semester->id)->first())->final_marks;
                                                            }
                                                            $existingScores = collect($termScores)->filter(fn($score) => $score !== null);
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
                                            $attendanceSummary = isset($attendanceSummaries) ? $attendanceSummaries->get($semester->id) : null;
                                            $comment = isset($comments) ? $comments->get($semester->id) : null;
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
                                            <div class="report-comments-grid">
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
                                        </div>
                                    @endforeach
                                </div>

                                <div class="report-footer">
                                    <div class="report-footer-left">
                                        {{ $schoolName }}<br>
                                        Generated on {{ now()->format('d F Y, h:i A') }}. Please retain for your records.
                                    </div>
                                    <div class="report-footer-stamp">Official Report</div>
                                </div>
                            </div>
                        @endif

                        @include('layouts.footer')
                    </div>
                </div>
            </div>
        </div>
    </div>

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
