@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
                    <h1 class="h3 mb-0 text-gray-800">Expense Management</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                        <i class="bi bi-plus-circle"></i> Request Expense
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
                        <h6 class="m-0 font-weight-bold text-primary">Expense Workflow Logs</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Details</th>
                                        <th>Requested By</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expenses as $expense)
                                        <tr>
                                            <td>{{ $expense->expense_date->format('d M Y') }}</td>
                                            <td>
                                                <strong>{{ $expense->title }}</strong><br>
                                                <small class="text-muted">{{ $expense->description }}</small>
                                                @if($expense->status == 'corrected')
                                                    <div class="mt-1 border-top pt-1">
                                                        <small class="text-info">Initial:
                                                            {{ $expense->initial_description }}</small>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>{{ $expense->requester->first_name ?? 'N/A' }}
                                                {{ $expense->requester->last_name ?? '' }}</td>
                                            <td>
                                                <span
                                                    class="font-weight-bold text-danger">₦{{ number_format($expense->amount, 2) }}</span>
                                                @if($expense->status == 'corrected')
                                                    <br><del
                                                        class="text-muted small">₦{{ number_format($expense->initial_amount, 2) }}</del>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $badgeClass = [
                                                        'pending' => 'bg-warning',
                                                        'approved' => 'bg-success',
                                                        'rejected' => 'bg-danger',
                                                        'corrected' => 'bg-info'
                                                    ][$expense->status] ?? 'bg-secondary';
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ ucfirst($expense->status) }}</span>
                                                @if($expense->approver)
                                                    <br><small class="text-muted">By: {{ $expense->approver->first_name }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    @if(Auth::user()->role == 'admin' && $expense->status == 'pending')
                                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                            data-bs-target="#approveModal{{$expense->id}}" title="Approve">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                            data-bs-target="#correctModal{{$expense->id}}" title="Correct">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                            data-bs-target="#rejectModal{{$expense->id}}" title="Reject">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    @endif

                                                    @if($expense->status == 'pending' || Auth::user()->role == 'admin')
                                                        <form action="{{ route('accounting.expenses.destroy', $expense->id) }}"
                                                            method="POST" class="d-inline"
                                                            onsubmit="return confirm('Delete this request?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="btn btn-link text-danger btn-sm p-0 ms-2"><i
                                                                    class="bi bi-trash"></i></button>
                                                        </form>
                                                    @endif
                                                </div>

                                                <!-- Approve Modal -->
                                                <div class="modal fade" id="approveModal{{$expense->id}}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form
                                                                action="{{ route('accounting.expenses.updateStatus', $expense->id) }}"
                                                                method="POST">
                                                                @csrf
                                                                <input type="hidden" name="status" value="approved">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Approve Expense</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>Are you sure you want to approve
                                                                        <strong>₦{{ number_format($expense->amount, 2) }}</strong>
                                                                        for "{{ $expense->title }}"?</p>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Approval Notes</label>
                                                                        <textarea name="notes" class="form-control"
                                                                            rows="2"></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-success">Confirm
                                                                        Approval</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Correct Modal -->
                                                <div class="modal fade" id="correctModal{{$expense->id}}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form
                                                                action="{{ route('accounting.expenses.correct', $expense->id) }}"
                                                                method="POST">
                                                                @csrf
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Correct & Approve</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">New Amount (₦)</label>
                                                                        <input type="number" step="0.01" name="amount"
                                                                            value="{{ $expense->amount }}" class="form-control"
                                                                            required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Update Description</label>
                                                                        <textarea name="description" class="form-control"
                                                                            rows="2"
                                                                            required>{{ $expense->description }}</textarea>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Correction
                                                                            Reason/Notes</label>
                                                                        <textarea name="notes" class="form-control" rows="2"
                                                                            placeholder="Explain why you corrected this..."></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-info">Update &
                                                                        Approve</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Reject Modal -->
                                                <div class="modal fade" id="rejectModal{{$expense->id}}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form
                                                                action="{{ route('accounting.expenses.updateStatus', $expense->id) }}"
                                                                method="POST">
                                                                @csrf
                                                                <input type="hidden" name="status" value="rejected">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Reject Expense</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p class="text-danger">You are rejecting this expense
                                                                        request.</p>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Rejection Reason</label>
                                                                        <textarea name="notes" class="form-control" rows="2"
                                                                            required></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-danger">Confirm
                                                                        Reject</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-center">
                                {{ $expenses->links() }}
                            </div>
                        </div>
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="addExpenseModal" tabindex="-1" role="dialog" aria-labelledby="addExpenseModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addExpenseModalLabel">Request New Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('accounting.expenses.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" placeholder="e.g. Office Supplies"
                                required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="expense_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="expense_date" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="amount" class="form-label">Amount (₦) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" name="amount" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
