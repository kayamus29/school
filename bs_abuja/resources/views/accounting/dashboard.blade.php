@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
                    <h1 class="h3 mb-0 text-gray-800">Accounting Dashboard</h1>
                    <form class="d-none d-sm-inline-block form-inline ml-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print Report</button>
                        </div>
                    </form>
                </div>

                @if(isset($error))
                    <div class="alert alert-danger">{{ $error }}</div>
                @else
                    <!-- Content Row -->
                    <div class="row">
                        <!-- Total Expected Fees -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Expected Fees (Session)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">₦{{ number_format($totalExpectedFees, 2) }}</div>
                                            <div class="text-xs text-muted">From {{ $activePayingStudents }} Active Students</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bi bi-calendar2-check fa-2x text-gray-300" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Received -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Received</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">₦{{ number_format($totalReceived, 2) }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bi bi-currency-dollar fa-2x text-gray-300" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Receivables (Debt) -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Receivables (Debt)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">₦{{ number_format(abs($totalReceivables), 2) }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bi bi-arrow-down-circle fa-2x text-gray-300" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Liabilities (Prepaid Credit) -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Prepaid Credits (Liabilities)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">₦{{ number_format($totalLiabilities, 2) }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bi bi-wallet2 fa-2x text-gray-300" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Expenses -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Expenses</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">₦{{ number_format($totalExpenses, 2) }}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bi bi-cart-x fa-2x text-gray-300" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Net Balance -->
                        <div class="col-md-12 mb-4">
                            <div class="card shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <h4 class="small font-weight-bold">Net Balance (Income - Expense) <span class="float-right {{ $netBalance >= 0 ? 'text-success' : 'text-danger' }}">₦{{ number_format($netBalance, 2) }}</span></h4>
                                            <div class="progress mb-4">
                                                @php
                                                    $percentage = ($totalReceived > 0) ? ($totalExpenses / $totalReceived) * 100 : 0;
                                                    $percentage = min(100, max(0, $percentage));
                                                @endphp
                                                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{$percentage}}" aria-valuemin="0" aria-valuemax="100"></div>
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ 100 - $percentage }}%" aria-valuenow="{{100 - $percentage}}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Recent Payments -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Recent Fee Collections</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Student</th>
                                                    <th>Class</th>
                                                    <th>Amount</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentPayments as $payment)
                                                    <tr>
                                                        <td>{{ $payment->student->first_name ?? 'N/A' }} {{ $payment->student->last_name ?? '' }}</td>
                                                        <td>{{ $payment->schoolClass->class_name ?? 'N/A' }}</td>
                                                        <td class="text-success">+₦{{ number_format($payment->amount_paid, 2) }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($payment->transaction_date)->format('d M Y') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Expenses -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Recent Expenses</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Amount</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentExpenses as $expense)
                                                    <tr>
                                                        <td>{{ $expense->title }}</td>
                                                        <td class="text-danger">-₦{{ number_format($expense->amount, 2) }}</td>
                                                        <td>{{ $expense->expense_date->format('d M Y') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
