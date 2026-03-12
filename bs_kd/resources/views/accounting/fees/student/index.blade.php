@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
                    <h1 class="h3 mb-0 text-gray-800">Fee Addons</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignFeeModal">
                        <i class="bi bi-plus-circle"></i> Add Addon
                    </button>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Assigned Addons</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Fee Head</th>
                                        <th>Session/Term</th>
                                        <th>Total Amount</th>
                                        <th>Paid</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($studentFees as $fee)
                                        <tr>
                                            <td>{{ $fee->student->first_name }} {{ $fee->student->last_name }}</td>
                                            <td>{{ $fee->feeHead->name }}</td>
                                            <td>
                                                {{ $fee->session->session_name }} / {{ $fee->semester->semester_name }}
                                            </td>
                                            <td class="fw-bold">₦{{ number_format($fee->amount, 2) }}</td>
                                            <td class="text-success">₦{{ number_format($fee->amount_paid, 2) }}</td>
                                            @php
                                                $snapshot = $fee->transaction->running_balance ?? null;
                                                $isCredit = $snapshot >= 0;
                                                $wColor = $isCredit ? 'text-success' : 'text-danger';
                                            @endphp
                                            <td class="{{ $wColor }} fw-bold">
                                                @if($snapshot !== null)
                                                    ₦{{ number_format(abs($snapshot), 2) }} {{ $isCredit ? '(Cr)' : '(Dr)' }}
                                                @else
                                                    <small class="text-muted">N/A</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($fee->status == 'paid')
                                                    <span class="badge bg-success">Charged</span>
                                                @elseif($fee->status == 'partial')
                                                    <span class="badge bg-warning text-dark">Partial</span>
                                                @else
                                                    <span class="badge bg-danger">Owing</span>
                                                @endif
                                            </td>
                                            <td>{{ $fee->description ?? '-' }}</td>
                                            <td>
                                                <form action="{{ route('accounting.fees.student.destroy', $fee->id) }}"
                                                    method="POST" onsubmit="return confirm('Are you sure?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No addons assigned yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $studentFees->links() }}
                        </div>
                    </div>
                </div>

                @include('layouts.footer')
            </div>
        </div>
    </div>

    <!-- Assign Fee Modal -->
    <div class="modal fade" id="assignFeeModal" tabindex="-1" aria-labelledby="assignFeeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('accounting.fees.student.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="assignFeeModalLabel">Assign Addon</h5>
                        <button type="button" class="btn-close" data-bs-close="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="student_id" class="form-label">Select Student</label>
                            <select name="student_id" id="student_id" class="form-select select2" required>
                                <option value="">-- Choose Student --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->first_name }} {{ $student->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="fee_head_id" class="form-label">Fee Head</label>
                            <select name="fee_head_id" id="fee_head_id" class="form-select" required>
                                <option value="">-- Choose Fee Head --</option>
                                @foreach($feeHeads as $head)
                                    <option value="{{ $head->id }}">{{ $head->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="session_id" class="form-label">Session</label>
                                <select name="session_id" id="session_id" class="form-select" required>
                                    @foreach($sessions as $session)
                                        <option value="{{ $session->id }}" {{ $session->id == $current_session_id ? 'selected' : '' }}>{{ $session->session_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="semester_id" class="form-label">Term/Semester</label>
                                <select name="semester_id" id="semester_id" class="form-select" required>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}">{{ $semester->semester_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount (₦)</label>
                            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-close="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Assign Addon</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
