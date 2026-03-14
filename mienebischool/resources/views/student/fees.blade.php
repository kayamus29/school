@extends('layouts.app')

@section('content')
    <div class="container text-darkings">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
                    <h1 class="h3 mb-0 text-gray-800">My Financial History</h1>
                </div>

                <div class="row">
                    <!-- Summary Cards -->
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Paid
                                            (Lifetime)</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            ₦{{ number_format($payments->sum('amount_paid'), 2) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-md-6 mb-4">
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

                <!-- Fees per Term -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Fees & Add-ons breakdown</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Term / Session</th>
                                        <th>Reference</th>
                                        <th>Amount</th>
                                        <th>Paid</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($fees as $fee)
                                        <tr>
                                            <td>{{ $fee->semester->semester_name }} ({{ $fee->session->session_name }})</td>
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
                                            <td colspan="6" class="text-center">No fee records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Payments -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">My Payments</h6>
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
                                        <th>Fee Item</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $payment)
                                        <tr>
                                            <td>{{ $payment->transaction_date }}</td>
                                            <td><code>{{ $payment->reference_no }}</code></td>
                                            <td class="font-weight-bold">₦{{ number_format($payment->amount_paid, 2) }}</td>
                                            <td>{{ $payment->payment_method }}</td>
                                            <td>{{ $payment->studentFee->feeHead->name ?? 'Universal Payment' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No payments found.</td>
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
