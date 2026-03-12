@extends('layouts.app')

@section('content')
    <div class="container text-darkings">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
                    <h1 class="h3 mb-0 text-gray-800">Financial Profile: {{ $student->first_name }}
                        {{ $student->last_name }}
                    </h1>
                    <div>
                        <a href="{{ route('student.profile.show', $student->id) }}" class="btn btn-secondary">
                            <i class="bi bi-person"></i> Basic Profile
                        </a>
                        <a href="{{ route('accounting.payments.create', ['student_id' => $student->id]) }}"
                            class="btn btn-success">
                            <i class="bi bi-cash"></i> Collect Payment
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Summary Cards -->
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Fees
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            ₦{{ number_format($student->getTotalFees(), 2) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-wallet2 fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Paid
                                            (Lifetime)
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            ₦{{ number_format($payments->sum('amount_paid'), 2) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-{{ $walletBalance >= 0 ? 'success' : 'danger' }} shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div
                                            class="text-xs font-weight-bold text-{{ $walletBalance >= 0 ? 'success' : 'danger' }} text-uppercase mb-1">
                                            Wallet Balance ({{ $walletBalance >= 0 ? 'Credit' : 'Debt' }})</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            ₦{{ number_format(abs($walletBalance), 2) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-wallet2 fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fee Breakdown -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Fee Breakdown (By Term)</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Term / Session</th>
                                        <th>Fee Type</th>
                                        <th>Reference</th>
                                        <th>Expected</th>
                                        <th>Paid</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($fees as $fee)
                                        <tr>
                                            <td>{{ $fee->semester->semester_name }} ({{ $fee->session->session_name }})</td>
                                            <td>{{ ucfirst($fee->fee_type) }}</td>
                                            <td>{{ $fee->reference ?? $fee->feeHead->name }}</td>
                                            <td>₦{{ number_format($fee->amount, 2) }}</td>
                                            <td class="text-success">₦{{ number_format($fee->amount_paid, 2) }}</td>
                                            <td class="text-danger">₦{{ number_format($fee->balance, 2) }}</td>
                                            <td>
                                                @if($fee->status == 'paid')
                                                    <span class="badge bg-success">Paid</span>
                                                @elseif($fee->status == 'partial')
                                                    <span class="badge bg-warning text-dark">Partial</span>
                                                @else
                                                    <span class="badge bg-danger">Owing</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No fee records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Payment Timeline -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Payment History</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Reference</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Received By</th>
                                        <th>Fee Linked</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $payment)
                                        <tr>
                                            <td>{{ $payment->transaction_date }}</td>
                                            <td><code>{{ $payment->reference_no }}</code></td>
                                            <td class="font-weight-bold">₦{{ number_format($payment->amount_paid, 2) }}</td>
                                            <td>{{ $payment->payment_method }}</td>
                                            <td>{{ $payment->receiver->first_name ?? 'System' }}</td>
                                            <td>{{ $payment->studentFee->feeHead->name ?? 'N/A' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No payments found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
