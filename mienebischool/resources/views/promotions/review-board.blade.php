@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-2">
                    <div class="col ps-4">
                        <h1 class="display-6 mb-3">
                            <i class="bi bi-shield-check"></i> Promotion Review Board
                        </h1>
                        <nav aria-label="breadcrumb" class="mb-4">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{route('promotions.index')}}">Promotions</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Review Board</li>
                            </ol>
                        </nav>
                        @include('session-messages')

                        <!-- Filers -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <form action="{{ route('promotions.review') }}" method="GET"
                                    class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label">Class</label>
                                        <select name="class_id" class="form-select" onchange="getSections(this.value)"
                                            required>
                                            <option value="">Select Class</option>
                                            @foreach($classes as $c)
                                                <option value="{{ $c->id }}" {{ $class_id == $c->id ? 'selected' : '' }}>
                                                    {{ $c->class_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Section</label>
                                        <select name="section_id" id="section_id" class="form-select" required>
                                            <option value="">Select Section</option>
                                            @foreach($sections as $s)
                                                <option value="{{ $s->id }}" {{ $section_id == $s->id ? 'selected' : '' }}>
                                                    {{ $s->section_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bi bi-search"></i> Load Data
                                        </button>
                                    </div>
                                    @if(count($reviews) > 0 && Auth::user()->hasAnyRole(['Admin', 'Teacher', 'Super Admin']))
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-success w-100" data-bs-toggle="modal"
                                                data-bs-target="#finalizeModal">
                                                <i class="bi bi-check-all"></i> Finalize Batch
                                            </button>
                                        </div>
                                    @endif
                                </form>
                            </div>
                        </div>

                        @if(count($reviews) > 0)
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th class="ps-3">Student</th>
                                                    <th>Cumulative Avg</th>
                                                    <th>Calculated Status</th>
                                                    <th>Final Status</th>
                                                    <th>Overrides/Comments</th>
                                                    <th class="text-end pe-3">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($reviews as $review)
                                                    <tr id="review-row-{{ $review->id }}"
                                                        class="{{ $review->is_finalized ? 'table-light opacity-75' : '' }}">
                                                        <td class="ps-3">
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-sm bg-light text-primary rounded-circle d-flex align-items-center justify-content-center me-2"
                                                                    style="width: 32px; height: 32px;">
                                                                    <i class="bi bi-person"></i>
                                                                </div>
                                                                <div>
                                                                    <div class="fw-bold">{{ $review->student->first_name }}
                                                                        {{ $review->student->last_name }}
                                                                    </div>
                                                                    <small class="text-muted">ID:
                                                                        {{ $review->student->id_card_number }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div
                                                                class="fw-bold {{ $review->calculated_average >= 50 ? 'text-success' : 'text-danger' }}">
                                                                {{ number_format($review->calculated_average, 2) }}%
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span
                                                                class="badge {{ $review->calculated_status == 'promoted' ? 'bg-success' : ($review->calculated_status == 'probation' ? 'bg-warning' : 'bg-danger') }}">
                                                                {{ strtoupper($review->calculated_status) }}
                                                            </span>
                                                        </td>
                                                        <td id="final-status-badge-{{ $review->id }}">
                                                            <span
                                                                class="badge {{ $review->final_status == 'promoted' ? 'bg-success' : ($review->final_status == 'probation' ? 'bg-warning' : 'bg-danger') }}">
                                                                {{ strtoupper($review->final_status) }}
                                                            </span>
                                                            @if($review->is_overridden)
                                                                <i class="bi bi-exclamation-triangle-fill text-warning ms-1"
                                                                    title="Manually Overridden"></i>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <small class="text-muted text-truncate d-block"
                                                                style="max-width: 200px;" title="{{ $review->override_comment }}">
                                                                {{ $review->override_comment ?: 'No comments' }}
                                                            </small>
                                                        </td>
                                                        <td class="text-end pe-3">
                                                            @if(!$review->is_finalized)
                                                                <div class="btn-group btn-group-sm">
                                                                    <button class="btn btn-outline-success"
                                                                        onclick="quickApprove({{ $review->id }}, '{{ $review->calculated_status }}')"
                                                                        title="Approve Calculated Status">
                                                                        <i class="bi bi-check-lg"></i>
                                                                    </button>
                                                                    <button class="btn btn-outline-secondary"
                                                                        onclick="openOverrideModal({{ $review->id }}, '{{ $review->final_status }}', '{{ $review->override_comment }}')"
                                                                        title="Manual Override">
                                                                        <i class="bi bi-pencil-square"></i>
                                                                    </button>
                                                                </div>
                                                            @else
                                                                <span class="text-success small fw-bold"><i class="bi bi-lock-fill"></i>
                                                                    Finalized</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @elseif($class_id && $section_id)
                            <div class="text-center py-5 bg-white shadow-sm rounded">
                                <i class="bi bi-inbox text-muted display-1"></i>
                                <p class="mt-3 text-muted">No students found for this section. Check your
                                    promotions/assignments.</p>
                            </div>
                        @endif

                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>

    <!-- Override Modal -->
    <div class="modal fade" id="overrideModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title">Manual Review & Override</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modal_review_id">
                    <div class="mb-3">
                        <label class="form-label">Review Status</label>
                        <select id="modal_final_status" class="form-select">
                            <option value="promoted">Promoted</option>
                            <option value="retained">Retained</option>
                            <option value="probation">Probation</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teacher Comments / Rationale</label>
                        <textarea id="modal_override_comment" class="form-control" rows="3"
                            placeholder="Explain why you are overriding or confirming the automated result..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitOverride()">Save Decision</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Finalize Modal -->
    <div class="modal fade" id="finalizeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('promotions.review.finalize') }}" method="POST">
                @csrf
                <input type="hidden" name="class_id" value="{{ $class_id }}">
                <input type="hidden" name="section_id" value="{{ $section_id }}">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Finalize Promotion Batch</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill"></i> WARNING: This action
                            is irreversible.</p>
                        <p>Once finalized:</p>
                        <ol>
                            <li>Students will be officially marked for promotion/retention.</li>
                            <li>Promotion records will be updated.</li>
                            <li>Financial arrears will be carried forward.</li>
                            <li>This batch will be LOCKED and cannot be edited.</li>
                        </ol>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Finalize & Lock Everything</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function getSections(class_id) {
            if (!class_id) return;
            fetch("{{ route('get.sections.courses.by.classId') }}?class_id=" + class_id)
                .then(res => res.json())
                .then(data => {
                    let html = '<option value="">Select Section</option>';
                    data.sections.forEach(s => {
                        html += `<option value="${s.id}">${s.section_name}</option>`;
                    });
                    document.getElementById('section_id').innerHTML = html;
                });
        }

        let currentOverrideModal = null;
        function openOverrideModal(reviewId, currentStatus, currentComment) {
            document.getElementById('modal_review_id').value = reviewId;
            document.getElementById('modal_final_status').value = currentStatus;
            document.getElementById('modal_override_comment').value = currentComment;

            if (!currentOverrideModal) {
                currentOverrideModal = new bootstrap.Modal(document.getElementById('overrideModal'));
            }
            currentOverrideModal.show();
        }

        function quickApprove(reviewId, calculatedStatus) {
            if (!confirm('Confirm the SYSTEM CALCULATED status: ' + calculatedStatus.toUpperCase() + '?\n\n(This will discard any manual overrides)')) return;

            const data = {
                review_id: reviewId,
                final_status: calculatedStatus,
                override_comment: 'Approved by Teacher', // Default comment to pass validation
                _token: '{{ csrf_token() }}'
            };

            submitReviewData(data);
        }

        function submitOverride() {
            const data = {
                review_id: document.getElementById('modal_review_id').value,
                final_status: document.getElementById('modal_final_status').value,
                override_comment: document.getElementById('modal_override_comment').value,
                _token: '{{ csrf_token() }}'
            };

            submitReviewData(data);
        }

        function submitReviewData(data) {
            fetch("{{ route('promotions.review.update') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
                .then(async res => {
                    const json = await res.json();
                    if (!res.ok) {
                        let errorMsg = json.message || 'Failed to save';
                        if (json.errors) {
                            errorMsg += '\n' + Object.values(json.errors).flat().join('\n');
                        }
                        throw new Error(errorMsg);
                    }
                    return json;
                })
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(err => {
                    alert('Error: ' + err.message);
                });
        }
    </script>

    <style>
        .avatar-sm {
            font-size: 1.2rem;
        }

        .bg-light-info {
            background-color: #e7f3ff;
        }

        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 4px;
        }
    </style>
@endsection