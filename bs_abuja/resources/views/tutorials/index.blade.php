@extends('layouts.app')

@section('content')
    @php
        $user = Auth::user();
        $isAdmin = $user->hasRole('Admin');
        $isTeacher = $user->hasRole('Teacher');
        $isAccountant = $user->hasRole('Accountant') || $user->role == 'accountant';
        $isStaff = $user->hasRole('Staff') || $user->role == 'staff';
    @endphp

    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-2">
                    <div class="col ps-4">
                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3">
                            <div>
                                <h1 class="display-6 mb-1">
                                    <i class="bi bi-journal-bookmark"></i> Staff Tutorials
                                </h1>
                                <p class="text-muted mb-0">Role-based guides for using the school app correctly.</p>
                            </div>
                        </div>

                        @include('session-messages')

                        <div class="alert alert-info shadow-sm border-0">
                            <strong>Start here:</strong> Use the quick links below, then read the section that matches your role. The same user can read multiple sections if you handle more than one job.
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <a href="#general-guide" class="text-decoration-none">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title mb-2"><i class="bi bi-compass"></i> General</h5>
                                            <p class="card-text text-muted mb-0">Session browsing, navigation, and shared rules.</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="#admin-guide" class="text-decoration-none">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title mb-2"><i class="bi bi-shield-lock"></i> Admin</h5>
                                            <p class="card-text text-muted mb-0">Setup, promotion, results oversight, and rollover.</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="#teacher-guide" class="text-decoration-none">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title mb-2"><i class="bi bi-person-workspace"></i> Teacher</h5>
                                            <p class="card-text text-muted mb-0">Attendance, marks, reports, lesson plans, and reviews.</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="#accounting-guide" class="text-decoration-none">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title mb-2"><i class="bi bi-cash-coin"></i> Accounting</h5>
                                            <p class="card-text text-muted mb-0">Bills, fees, payments, and expenses.</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-12" id="general-guide">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <h3 class="h4 mb-3">General Guide</h3>
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <h6>How the app is organized</h6>
                                                <ul class="mb-0">
                                                    <li>Every record works inside a school session and semester.</li>
                                                    <li>Use Academic Settings to create a session, create a semester, and browse older sessions.</li>
                                                    <li>When browsing an older session, treat the data as historical unless you intentionally need to edit it.</li>
                                                    <li>Students are permanent users. They are not recreated every term or every session.</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Current key rules</h6>
                                                <ul class="mb-0">
                                                    <li>Creating a new term now copies forward subjects and teacher assignments from the previous term in the same session.</li>
                                                    <li>Rolling over a new session copies structure only. Student placement still happens through promotion.</li>
                                                    <li>Results pages now include attendance summary, and class teachers can save affective scores.</li>
                                                    <li>Lesson plans support typed content and file uploads.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12" id="admin-guide">
                                <div class="card border-0 shadow-sm {{ $isAdmin ? 'border-start border-4 border-primary' : '' }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h3 class="h4 mb-0">Admin Tutorial</h3>
                                            @if($isAdmin)
                                                <span class="badge bg-primary">Recommended For You</span>
                                            @endif
                                        </div>

                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <h6>Starting a new term in the same session</h6>
                                                <ol class="mb-0">
                                                    <li>Open Academic Settings and create the new semester.</li>
                                                    <li>Confirm the copied subjects and teacher assignments look correct.</li>
                                                    <li>Set the term total school days in Total School Days.</li>
                                                    <li>Confirm attendance type and any academic settings before teachers begin work.</li>
                                                </ol>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Starting a new academic session</h6>
                                                <ol class="mb-0">
                                                    <li>Use Roll Over New Session from Academic Settings.</li>
                                                    <li>Check copied semesters, classes, sections, subjects, and teacher assignments.</li>
                                                    <li>Review promotion policies and make corrections where needed.</li>
                                                    <li>Run promotion after the structure is confirmed.</li>
                                                </ol>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Promotion workflow</h6>
                                                <ul class="mb-0">
                                                    <li>Promotion Review Board is the academic decision stage.</li>
                                                    <li>The system calculates `promoted`, `probation`, or `retained` from marks and policy.</li>
                                                    <li>Teacher or admin can confirm or override before finalizing decisions.</li>
                                                    <li>Manual Promotion is the placement stage into the latest session, class, and section.</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Results and oversight</h6>
                                                <ul class="mb-0">
                                                    <li>Use Admin Results to inspect a student report across subjects.</li>
                                                    <li>Attendance summary shows school opened, days present, and attendance rate.</li>
                                                    <li>Affective scores appear on the results page after the class teacher saves them.</li>
                                                    <li>Lesson Plans lets admin view teacher-submitted plans and attached files.</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Student management</h6>
                                                <ul class="mb-0">
                                                    <li>Add student accounts only once.</li>
                                                    <li>Use promotion to place existing students into a new session.</li>
                                                    <li>Student subject removal is handled from the student profile when a class/section teacher needs an exception.</li>
                                                    <li>Use Browse Session carefully when checking prior-year data.</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Deployment note</h6>
                                                <ul class="mb-0">
                                                    <li>After pulling code on live, visit `/deploy/migrate` with the configured key or while logged in as admin.</li>
                                                    <li>The migration route runs `php artisan migrate --force`.</li>
                                                    <li>Remove or rotate `DEPLOY_MIGRATE_KEY` after deployment.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12" id="teacher-guide">
                                <div class="card border-0 shadow-sm {{ $isTeacher ? 'border-start border-4 border-success' : '' }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h3 class="h4 mb-0">Teacher Tutorial</h3>
                                            @if($isTeacher)
                                                <span class="badge bg-success">Recommended For You</span>
                                            @endif
                                        </div>

                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <h6>Daily teaching workflow</h6>
                                                <ol class="mb-0">
                                                    <li>Open My Courses and confirm your current class and subject assignments.</li>
                                                    <li>Take attendance based on the school attendance mode.</li>
                                                    <li>Enter marks and submit final marks when your course is complete.</li>
                                                    <li>Use the Results area to review subject and section performance.</li>
                                                </ol>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Class teacher responsibilities</h6>
                                                <ul class="mb-0">
                                                    <li>Open the section results page to review each student report.</li>
                                                    <li>Save affective scores for punctuality, neatness, politeness, honesty, performance, attentiveness, perseverance, speaking, and writing.</li>
                                                    <li>If needed, override a student's attendance summary for that term.</li>
                                                    <li>Use the student profile page to remove a subject from a specific student where the class/section needs that exception.</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Lesson plans</h6>
                                                <ul class="mb-0">
                                                    <li>Open Lesson Plans from My Courses or the main menu.</li>
                                                    <li>Create a plan by typing the content directly or uploading `pdf`, `doc`, or `docx`.</li>
                                                    <li>Give the lesson plan a clear title and attach it to the correct class, section, and course context where needed.</li>
                                                    <li>Admin can view all lesson plans after submission.</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Promotion review</h6>
                                                <ul class="mb-0">
                                                    <li>Use Promotion Review Board to inspect the system-calculated decision for each student.</li>
                                                    <li>Confirm the calculated status if it is correct.</li>
                                                    <li>Override it when school leadership decides differently.</li>
                                                    <li>Finalize decisions only after review is complete for the class and section.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12" id="accounting-guide">
                                <div class="card border-0 shadow-sm {{ $isAccountant ? 'border-start border-4 border-warning' : '' }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h3 class="h4 mb-0">Accounting Tutorial</h3>
                                            @if($isAccountant)
                                                <span class="badge bg-warning text-dark">Recommended For You</span>
                                            @endif
                                        </div>

                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <h6>Fees and billing</h6>
                                                <ul class="mb-0">
                                                    <li>Create fee heads first.</li>
                                                    <li>Assign class fees for the current term or use bulk billing where appropriate.</li>
                                                    <li>Apply student-specific fees only when a bill should not affect the whole class.</li>
                                                    <li>Confirm outstanding balances before collecting or adjusting payments.</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Payments and expenses</h6>
                                                <ul class="mb-0">
                                                    <li>Use the Payments area to record and review receipts.</li>
                                                    <li>Use Expenses to enter, approve, correct, or remove expense records according to your permissions.</li>
                                                    <li>Check analytics and debtors regularly for outstanding financial issues.</li>
                                                    <li>Use session browsing carefully when reviewing old financial records.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12" id="staff-guide">
                                <div class="card border-0 shadow-sm {{ $isStaff ? 'border-start border-4 border-secondary' : '' }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h3 class="h4 mb-0">Support Staff Tutorial</h3>
                                            @if($isStaff)
                                                <span class="badge bg-secondary">Recommended For You</span>
                                            @endif
                                        </div>

                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <h6>Daily use</h6>
                                                <ul class="mb-0">
                                                    <li>Use Check-In / Out to record staff attendance.</li>
                                                    <li>Open notices, events, and any assigned operational modules from the left menu.</li>
                                                    <li>Do not change academic-session data unless your role specifically requires it.</li>
                                                    <li>Ask admin before using session browsing if you are unsure which year you are viewing.</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Operational reminders</h6>
                                                <ul class="mb-0">
                                                    <li>Historical records may belong to older sessions.</li>
                                                    <li>Promotion, rollover, and academic settings should stay with academic management staff.</li>
                                                    <li>Use this page as a reference whenever a workflow is unclear.</li>
                                                    <li>Report incorrect permissions to the administrator rather than working around them.</li>
                                                </ul>
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
