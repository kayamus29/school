@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
                    <h1 class="h3 mb-0">My Expenses</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#createExpenseModal">
                        <i class="bi bi-plus-lg"></i> New Request
                    </button>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">Date</th>
                                        <th>Title</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-center">Status</th>
                                        <th>Remarks</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($expenses as $expense)
                                        <tr>
                                            <td class="ps-3">
                                                {{ \Carbon\Carbon::parse($expense->expense_date)->format('d M, Y') }}</td>
                                            <td>
                                                <div class="fw-bold">{{ $expense->title }}</div>
                                                <div class="small text-muted">{{ Str::limit($expense->description, 30) }}</div>
                                            </td>
                                            <td class="text-end">₦{{ number_format($expense->amount, 2) }}</td>
                                            <td class="text-center">
                                                @if($expense->status == 'approved')
                                                    <span class="badge bg-success">Approved</span>
                                                @elseif($expense->status == 'rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                @elseif($expense->status == 'corrected')
                                                    <span class="badge bg-info text-dark">Corrected</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                @endif
                                            </td>
                                            <td class="small text-muted">
                                                {{ $expense->approver_notes ?? '-' }}
                                            </td>
                                            <td class="text-end pe-3">
                                                @if($expense->status == 'pending')
                                                    <form action="{{ route('accounting.expenses.destroy', $expense->id) }}"
                                                        method="POST" onsubmit="return confirm('Are you sure?');" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                No expense requests found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0">
                        {{ $expenses->links() }}
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createExpenseModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('accounting.expenses.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">New Expense Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title / Item</label>
                            <input type="text" name="title" class="form-control" required
                                placeholder="e.g. Office Supplies">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₦</span>
                                <input type="number" step="0.01" name="amount" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="expense_date" class="form-control" required
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description / Reason</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection