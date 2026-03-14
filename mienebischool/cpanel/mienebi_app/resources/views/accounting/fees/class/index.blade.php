@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/accounting/class-fee-custom.css') }}">

    <div class="container pb-5">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mt-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('accounting.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Fee Architecture</li>
            </ol>
        </nav>
        <div class="row justify-content-start">
            @include('layouts.left-menu')

            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <!-- Summary Stats Header -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
                    <div>
                        <h1 class="h3 mb-1 text-gray-800 fw-bold">Fee Architecture</h1>
                        <p class="text-muted small mb-0">Manage fee definitions and execute bulk term billing across all
                            classes.</p>
                    </div>
                    <div class="d-flex gap-2 mt-3 mt-sm-0">
                        <button type="button" class="btn btn-primary shadow-sm px-4" data-bs-toggle="modal"
                            data-bs-target="#bulkBillingModal" aria-label="Open Bulk Billing Wizard">
                            <i class="bi bi-lightning-charge-fill me-1"></i> Execute Bulk Billing
                        </button>
                    </div>
                </div>

                <!-- Dashboard Brief -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body py-3">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Classes</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $classes->total() }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body py-3">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Avg. Completion</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">84%</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end justify-content-end pb-1">
                        <div class="input-group shadow-sm w-50">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" id="classSearch" class="form-control border-start-0"
                                placeholder="Filter classes...">
                        </div>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Main Navigation Tabs -->
                <ul class="nav nav-tabs border-0 mb-4 bg-white p-2 rounded shadow-sm gap-2" id="feeTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active border-0 rounded-pill px-4" id="definitions-tab" data-bs-toggle="tab"
                            data-bs-target="#definitions" type="button" role="tab">
                            <i class="bi bi-journal-check me-2"></i>Fee Definitions
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link border-0 rounded-pill px-4" id="history-tab" data-bs-toggle="tab"
                            data-bs-target="#history" type="button" role="tab">
                            <i class="bi bi-clock-history me-2"></i>Execution Audit
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="feeTabsContent">
                    <div class="tab-pane fade show active" id="definitions" role="tabpanel">
                        <!-- Global Table Controls -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="btn-group shadow-sm">
                                <button class="btn btn-sm btn-light border" id="expandAll">Expand All</button>
                                <button class="btn btn-sm btn-light border" id="collapseAll">Collapse All</button>
                            </div>
                            <span class="text-muted small">Session:
                                <strong>{{ $currentSession->session_name }}</strong></span>
                        </div>

                        <div class="table-responsive fee-table-desktop">
                            <table class="table fee-table align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="w-25">Class Name</th>
                                        @foreach($semesters as $semester)
                                            <th class="text-center">{{ $semester->semester_name }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($classes as $index => $class)
                                        <tr class="fee-row class-row" data-class-name="{{ $class->class_name }}">
                                            <td class="fee-cell">
                                                <div class="d-flex align-items-center">
                                                    <i
                                                        class="bi bi-building me-3 text-primary p-2 bg-light rounded shadow-sm"></i>
                                                    <div>
                                                        <div class="fw-bold text-dark">{{ $class->class_name }}</div>
                                                        <div class="text-muted smallest">ID: {{ $class->id }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            @foreach($semesters as $semester)
                                                @php
                                                    $feeItems = $class->getFeeItems($currentSession->id, $semester->id);
                                                    $total = $feeItems->sum('amount');
                                                    $count = $feeItems->count();
                                                    $targetId = "details-{$class->id}-{$semester->id}";
                                                @endphp
                                                <td class="fee-cell text-center">
                                                    <div class="d-flex flex-column align-items-center">
                                                        @if($count > 0)
                                                            <div class="h6 mb-1 fw-bold text-primary">₦{{ number_format($total) }}</div>
                                                            <div class="fee-progress-bar w-75 mb-2">
                                                                <div class="fee-progress-fill bg-success" style="width: 100%"></div>
                                                            </div>
                                                            <button
                                                                class="btn btn-sm btn-link p-0 text-decoration-none toggle-expand smallest"
                                                                data-target="{{ $targetId }}" aria-expanded="false">
                                                                <i class="bi bi-plus-circle me-1"></i> {{ $count }} Items
                                                            </button>
                                                        @else
                                                            <div class="h6 mb-1 text-muted">₦0</div>
                                                            <div class="fee-progress-bar w-75 mb-2">
                                                                <div class="fee-progress-fill bg-danger" style="width: 0%"></div>
                                                            </div>
                                                            <span class="text-danger smallest"><i class="bi bi-x-circle"></i>
                                                                Missing</span>
                                                        @endif

                                                        <button class="btn btn-sm btn-outline-primary mt-2 px-3 manage-term-btn"
                                                            data-class-name="{{ $class->class_name }}"
                                                            data-class-id="{{ $class->id }}" data-semester-id="{{ $semester->id }}"
                                                            data-semester-name="{{ $semester->semester_name }}"
                                                            data-session-id="{{ $currentSession->id }}" data-bs-toggle="modal"
                                                            data-bs-target="#assignFeeModal">
                                                            Manage
                                                        </button>
                                                    </div>

                                                    <!-- Expandable Itemization -->
                                                    <div id="{{ $targetId }}" class="fee-details-container mt-3 text-start">
                                                        <div class="bg-light p-2 rounded border small">
                                                            @foreach($feeItems as $item)
                                                                <div
                                                                    class="d-flex justify-content-between mb-1 border-bottom border-white pb-1">
                                                                    <span class="text-muted">{{ $item->feeHead->name }}</span>
                                                                    <span class="fw-bold">₦{{ number_format($item->amount) }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $classes->links() }}
                        </div>
                    </div>

                    <!-- History Tab -->
                    <div class="tab-pane fade" id="history" role="tabpanel">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Date Executed</th>
                                                <th>Term Context</th>
                                                <th class="text-center">Student Pool</th>
                                                <th>Revenue Impact</th>
                                                <th>Status</th>
                                                <th>Processor</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($billingHistory as $batch)
                                                <tr>
                                                    <td class="ps-4">
                                                        <div class="fw-bold text-dark">
                                                            {{ $batch->created_at->format('M d, Y') }}</div>
                                                        <div class="text-muted smaller">
                                                            {{ $batch->created_at->diffForHumans() }}</div>
                                                    </td>
                                                    <td>{{ $batch->session->session_name }} -
                                                        {{ $batch->semester->semester_name }}</td>
                                                    <td class="text-center"><span
                                                            class="badge bg-light text-dark border">{{ number_format($batch->student_count) }}</span>
                                                    </td>
                                                    <td class="fw-bold text-success">
                                                        ₦{{ number_format($batch->total_amount, 2) }}</td>
                                                    <td>
                                                        @if($batch->status == 'finalized')
                                                            <span class="badge bg-success-subtle text-success px-2 py-1"><i
                                                                    class="bi bi-shield-check me-1"></i> Finalized</span>
                                                        @else
                                                            <span
                                                                class="badge bg-warning-subtle text-warning px-2 py-1">Locked</span>
                                                        @endif
                                                    </td>
                                                    <td><i class="bi bi-person-circle me-1"></i>
                                                        {{ $batch->processor->first_name ?? 'System' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-5">No execution logs found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @include('layouts.footer')
            </div>
        </div>
    </div>

    <!-- AJAX Management Modal -->
    <div class="modal fade" id="assignFeeModal" data-bs-focus="false" tabindex="-1" aria-labelledby="manageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-white border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary-subtle p-3 rounded-circle me-3">
                            <i class="bi bi-gear-wide-connected text-primary fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold" id="manageModalLabel">Manage Fee Architecture</h5>
                            <p class="text-muted small mb-0"><span id="target-class" class="fw-bold">...</span> | <span
                                    id="target-term" class="text-primary fw-bold">...</span></p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 pt-4">
                    <div class="row g-4">
                        <!-- Feed Adding Form -->
                        <div class="col-md-5 border-end pe-md-4">
                            <h6 class="fw-bold mb-3">Add Fee Component</h6>
                            <form id="manageFeeForm">
                                <input type="hidden" name="class_id" id="modal_class_id">
                                <input type="hidden" name="session_id" id="modal_session_id">
                                <input type="hidden" name="semester_id" id="modal_semester_id">

                                <div class="mb-3">
                                    <label class="form-label smallest uppercase fw-bold text-muted">Fee Category</label>
                                    <select class="form-select border-light shadow-sm bg-light" name="fee_head_id" required>
                                        <option value="">-- Select --</option>
                                        @foreach($feeHeads as $h)
                                            <option value="{{ $h->id }}">{{ $h->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label smallest uppercase fw-bold text-muted">Amount (₦)</label>
                                    <div class="input-group shadow-sm">
                                        <span class="input-group-text bg-white border-light">₦</span>
                                        <input type="number" step="0.01" class="form-control border-light" name="amount"
                                            required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label smallest uppercase fw-bold text-muted">Description</label>
                                    <input type="text" class="form-control border-light shadow-sm bg-light"
                                        name="description" placeholder="Optional notes...">
                                </div>
                                <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                                    <i class="bi bi-plus-lg me-1"></i> Add to Term
                                </button>
                            </form>

                            <div class="mt-4 p-3 bg-light rounded border border-white">
                                <h6 class="smallest fw-bold uppercase text-muted mb-2"><i class="bi bi-lightbulb"></i> Pro
                                    Tip</h6>
                                <p class="smallest mb-0">Use the "Copy" tool to duplicate these fees to other terms or
                                    parallel classes in seconds.</p>
                            </div>
                        </div>

                        <!-- Live Fee List -->
                        <div class="col-md-7">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0">Current Components</h6>
                            </div>
                            <div id="modalFeeList" class="fee-item-list">
                                <!-- JS injected items -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3-Step Bulk Billing Wizard Modal -->
    <div class="modal fade" id="bulkBillingModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered shadow-lg">
            <div class="modal-content border-0">
                <div class="modal-header border-0 pb-0 shadow-sm">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning-subtle p-2 rounded me-3"><i
                                class="bi bi-lightning-charge text-warning fs-5"></i></div>
                        <h5 class="modal-title fw-bold">Billing Wizard</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body py-4">
                    <!-- Step Indicators -->
                    <div class="d-flex justify-content-between px-5 mb-4 position-relative">
                        <div class="position-absolute translate-middle-y top-50 start-0 end-0 border-top mt-1"
                            style="z-index: -1;"></div>
                        <div class="step-indicator active">1</div>
                        <div class="step-indicator">2</div>
                        <div class="step-indicator">3</div>
                    </div>

                    <!-- Wizard Steps -->
                    <div id="wizard-step-1" class="wizard-step active">
                        <h6 class="fw-bold text-center mb-4">Start New Billing Cycle</h6>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Select Target Term</label>
                            <select id="bulk_semester_id" class="form-select form-select-lg border shadow-sm" required>
                                <option value="">-- Choose Term --</option>
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id }}">{{ $semester->semester_name }}
                                        ({{ $currentSession->session_name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="alert alert-info py-2 smallest">
                            <i class="bi bi-info-circle me-1"></i> System will calculate fees based on definitions for the
                            selected term.
                        </div>
                        <input type="hidden" name="session_id" value="{{ $currentSession->id }}">
                    </div>

                    <div id="wizard-step-2" class="wizard-step">
                        <div id="wizard-preview-content">
                            <!-- AJAX Impact Preview -->
                        </div>
                    </div>

                    <div id="wizard-step-3" class="wizard-step text-center py-4">
                        <div class="bg-danger-subtle p-4 rounded-circle d-inline-block mb-3">
                            <i class="bi bi-shield-lock-fill text-danger fs-1 text-danger"></i>
                        </div>
                        <h5 class="fw-bold text-danger">Final Authorization</h5>
                        <p class="text-muted small">You are about to post ledger entries for all active students. This
                            operation is historical and permanent.</p>

                        <form action="{{ route('accounting.fees.class.bulk_bill') }}" method="POST">
                            @csrf
                            <input type="hidden" name="semester_id" id="final_semester_id">
                            <input type="hidden" name="session_id" value="{{ $currentSession->id }}">

                            <div class="form-check text-start d-inline-block mt-3 border p-3 rounded bg-light">
                                <input class="form-check-input ms-0 me-2" type="checkbox" id="confirmCheckbox" required>
                                <label class="form-check-label small fw-bold" for="confirmCheckbox">
                                    I confirm all fee definitions are audited and correct.
                                </label>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-danger btn-lg px-5 shadow fw-bold" id="authorize-btn"
                                    disabled>
                                    AUTHORIZE SYSTEM BILLING
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4 border" id="prevStep"
                        style="display: none;">Back</button>
                    <div class="flex-grow-1"></div>
                    <button type="button" class="btn btn-primary px-5 shadow" id="nextStep">Continue <i
                            class="bi bi-arrow-right ms-1"></i></button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/accounting/class-fee-manager.js') }}"></script>
@endsection