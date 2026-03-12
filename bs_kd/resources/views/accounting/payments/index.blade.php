@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
                    <h1 class="h3 mb-0 text-gray-800">Fee Payments</h1>
                    <a href="{{ route('accounting.payments.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Collect Payment
                    </a>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Payment History</h6>
                        <form action="{{ route('accounting.payments.index') }}" method="GET" class="d-flex">
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <input type="text" name="search" class="form-control" placeholder="Search Student/ID..."
                                    value="{{ request('search') }}">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('accounting.payments.index') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle"></i>
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Ref No</th>
                                        <th>Date</th>
                                        <th>Student</th>
                                        <th>Class</th>
                                        <th>Session/Term</th>
                                        <th>Amount Paid</th>
                                        <th>Wallet Balance</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                        <tr>
                                            <td>{{ $payment->reference_no }}</td>
                                            <td>{{ \Carbon\Carbon::parse($payment->transaction_date)->format('d M Y') }}</td>
                                            <td>{{ $payment->student->first_name ?? 'Unknown' }}
                                                {{ $payment->student->last_name ?? '' }}
                                            </td>
                                            <td>{{ $payment->schoolClass->class_name ?? '-' }}</td>
                                            <td>
                                                <small>{{ $payment->session->session_name ?? '' }}</small><br>
                                                <small class="text-muted">{{ $payment->semester->semester_name ?? '' }}</small>
                                            </td>
                                            <td>
                                                <span
                                                    class="font-weight-bold text-success">₦{{ number_format($payment->amount_paid, 2) }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $snapshot = $payment->transaction->running_balance ?? null;
                                                    $isCredit = $snapshot >= 0;
                                                    $color = $isCredit ? 'success' : 'danger';
                                                @endphp
                                                @if($snapshot !== null)
                                                    <span class="text-{{ $color }} font-weight-bold">
                                                        ₦{{ number_format(abs($snapshot), 2) }}
                                                        <small>({{ $isCredit ? 'Cr' : 'Dr' }})</small>
                                                    </span>
                                                @else
                                                    <small class="text-muted">N/A</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($snapshot !== null)
                                                    @if($snapshot >= 0)
                                                        <span class="badge bg-success">In Credit</span>
                                                    @else
                                                        <span class="badge bg-danger">Owing</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-secondary">Unknown</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('accounting.payments.show', $payment->id) }}"
                                                    class="btn btn-info btn-sm">
                                                    <i class="bi bi-eye"></i> Receipt
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-center">
                                {{ $payments->links() }}
                            </div>
                        </div>
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
